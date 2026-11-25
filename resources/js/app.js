const apiStatus = document.querySelector('#api-status');
const apiStatusText = document.querySelector('#api-status-text');
const addButton = document.querySelector('#add-product');
const searchInput = document.querySelector('#product-search');
const productList = document.querySelector('#product-list');
const productCount = document.querySelector('#product-count');
const tableCaption = document.querySelector('#table-caption');
const pageError = document.querySelector('#page-error');
const dialog = document.querySelector('#product-dialog');
const form = document.querySelector('#product-form');
const formError = document.querySelector('#form-error');
const toast = document.querySelector('#toast');

let products = [];
let toastTimeout;

async function apiRequest(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...options.headers,
        },
        ...options,
    });

    if (response.status === 204) {
        return null;
    }

    const result = await response.json();

    if (!response.ok) {
        const error = new Error(result.message || 'Permintaan ke API gagal.');
        error.validationErrors = result.errors || null;
        throw error;
    }

    return result;
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);
}

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(price));
}

function renderProducts() {
    const query = searchInput.value.trim().toLocaleLowerCase('id-ID');
    const visibleProducts = products.filter((product) =>
        `${product.name} ${product.category}`.toLocaleLowerCase('id-ID').includes(query),
    );

    productCount.textContent = products.length;
    tableCaption.textContent = `${visibleProducts.length} dari ${products.length} produk`;

    if (visibleProducts.length === 0) {
        productList.innerHTML = products.length
            ? '<tr><td class="table-message" colspan="5">Produk yang cocok tidak ditemukan.</td></tr>'
            : '<tr><td class="table-message" colspan="5"><strong>Belum ada produk</strong>Tambahkan produk pertama untuk mulai mengelola inventori.</td></tr>';
        return;
    }

    productList.innerHTML = visibleProducts.map((product) => {
        const stockClass = product.stock === 0 ? 'stock-empty' : product.stock < 5 ? 'stock-low' : '';
        const description = product.description
            ? `<span class="product-description">${escapeHtml(product.description)}</span>`
            : '';

        return `
            <tr>
                <td>
                    <div class="product-cell">
                        <span class="product-thumb" aria-hidden="true">${escapeHtml(product.name.charAt(0))}</span>
                        <span><span class="product-name">${escapeHtml(product.name)}</span>${description}</span>
                    </div>
                </td>
                <td><span class="category-tag">${escapeHtml(product.category)}</span></td>
                <td class="price-cell">${formatPrice(product.price)}</td>
                <td><span class="stock-badge ${stockClass}">${product.stock} unit</span></td>
                <td>
                    <div class="row-actions">
                        <button class="icon-button" type="button" data-action="edit" data-id="${product.id}" aria-label="Edit ${escapeHtml(product.name)}" title="Edit">✎</button>
                        <button class="icon-button delete-button" type="button" data-action="delete" data-id="${product.id}" aria-label="Hapus ${escapeHtml(product.name)}" title="Hapus">⌫</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

async function loadProducts() {
    const result = await apiRequest('/api/products');
    products = result.data;
    renderProducts();
    searchInput.disabled = false;
    addButton.disabled = false;
}

function setApiStatus(status, message) {
    apiStatus.classList.remove('is-online', 'is-offline');
    if (status !== 'checking') {
        apiStatus.classList.add(status === 'online' ? 'is-online' : 'is-offline');
    }
    apiStatusText.textContent = message;
}

function showToast(message, isError = false) {
    window.clearTimeout(toastTimeout);
    toast.textContent = message;
    toast.classList.toggle('toast-error', isError);
    toast.hidden = false;
    toastTimeout = window.setTimeout(() => {
        toast.hidden = true;
    }, 3500);
}

function openForm(product = null) {
    form.reset();
    formError.hidden = true;
    document.querySelector('#product-id').value = product?.id ?? '';
    document.querySelector('#dialog-title').textContent = product ? 'Edit produk' : 'Tambah produk';
    document.querySelector('#save-product').textContent = product ? 'Simpan perubahan' : 'Simpan produk';

    if (product) {
        for (const field of ['name', 'category', 'price', 'stock', 'description']) {
            form.elements.namedItem(field).value = product[field] ?? '';
        }
    }

    dialog.showModal();
    document.querySelector('#product-name').focus();
}

function showFormError(error) {
    const messages = error.validationErrors
        ? Object.values(error.validationErrors).flat().join(' ')
        : error.message;
    formError.textContent = messages;
    formError.hidden = false;
}

async function saveProduct(event) {
    event.preventDefault();
    const id = document.querySelector('#product-id').value;
    const fields = new FormData(form);
    const payload = {
        name: fields.get('name').trim(),
        category: fields.get('category').trim(),
        price: Number(fields.get('price')),
        stock: Number(fields.get('stock')),
        description: fields.get('description').trim() || null,
    };
    const submitButton = document.querySelector('#save-product');

    submitButton.disabled = true;
    formError.hidden = true;

    try {
        await apiRequest(id ? `/api/products/${id}` : '/api/products', {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(payload),
        });
        dialog.close();
        await loadProducts();
        showToast(id ? 'Perubahan produk berhasil disimpan.' : 'Produk berhasil ditambahkan.');
    } catch (error) {
        showFormError(error);
    } finally {
        submitButton.disabled = false;
    }
}

async function handleProductAction(event) {
    const button = event.target.closest('button[data-action]');
    if (!button) return;

    const product = products.find((item) => item.id === Number(button.dataset.id));
    if (!product) return;

    if (button.dataset.action === 'edit') {
        openForm(product);
        return;
    }

    if (!window.confirm(`Hapus produk "${product.name}"? Tindakan ini tidak dapat dibatalkan.`)) return;

    button.disabled = true;
    try {
        await apiRequest(`/api/products/${product.id}`, { method: 'DELETE' });
        await loadProducts();
        showToast('Produk berhasil dihapus.');
    } catch (error) {
        showToast(error.message, true);
        button.disabled = false;
    }
}

async function initialize() {
    setApiStatus('checking', 'Memeriksa API…');
    try {
        const health = await apiRequest('/api/health');
        if (health.status !== 'ok') {
            throw new Error('API belum siap digunakan.');
        }
        await loadProducts();
        setApiStatus('online', 'API terhubung');
        tableCaption.textContent = `${products.length} produk`;
    } catch (error) {
        setApiStatus('offline', 'API tidak tersedia');
        pageError.textContent = `Tidak dapat memuat produk: ${error.message} Pastikan server dan database Laravel aktif, lalu muat ulang halaman.`;
        pageError.hidden = false;
        productList.innerHTML = '<tr><td class="table-message" colspan="5">Produk tidak dapat dimuat sebelum API siap.</td></tr>';
        productCount.textContent = '—';
        tableCaption.textContent = 'Koneksi API gagal';
    }
}

addButton.addEventListener('click', () => openForm());
document.querySelector('#close-dialog').addEventListener('click', () => dialog.close());
document.querySelector('#cancel-dialog').addEventListener('click', () => dialog.close());
form.addEventListener('submit', saveProduct);
productList.addEventListener('click', handleProductAction);
searchInput.addEventListener('input', renderProducts);

initialize();
