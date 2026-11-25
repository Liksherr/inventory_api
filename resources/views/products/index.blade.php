<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventori — Kelola Produk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="/" aria-label="Inventori, beranda">
                <span class="brand-mark" aria-hidden="true">i</span>
                <span>inventori<span class="brand-period">.</span></span>
            </a>
            <p class="sidebar-label">MENU</p>
            <a class="nav-link nav-link-active" href="/" aria-current="page">
                <span aria-hidden="true">▦</span> Produk
            </a>
            <div class="sidebar-note">
                <span class="note-icon" aria-hidden="true">✦</span>
                <strong>Contoh aplikasi CRUD</strong>
                <p>Data produk tersimpan dan dikelola melalui RESTful API Laravel.</p>
            </div>
            <div class="sidebar-footer">
                <span class="avatar">A</span>
                <span><strong>Admin</strong><small>Pengelola toko</small></span>
            </div>
        </aside>

        <section class="main-content">
            <header class="topbar">
                <div class="breadcrumb">Workspace <span>/</span> <strong>Produk</strong></div>
                <div class="api-indicator" id="api-status" role="status" aria-live="polite">
                    <span class="status-dot"></span>
                    <span id="api-status-text">Memeriksa API…</span>
                </div>
            </header>

            <div class="page-content">
                <div class="page-heading">
                    <div>
                        <p class="eyebrow">MANAJEMEN INVENTORI</p>
                        <h1>Produk</h1>
                        <p class="page-subtitle">Atur katalog dan pantau stok produk tokomu.</p>
                    </div>
                    <button class="button button-primary" id="add-product" type="button" disabled>
                        <span aria-hidden="true">＋</span> Tambah produk
                    </button>
                </div>

                <section class="summary-card" aria-label="Ringkasan inventori">
                    <div class="summary-icon" aria-hidden="true">▤</div>
                    <div>
                        <p>Total produk</p>
                        <strong id="product-count">—</strong>
                    </div>
                    <span class="summary-caption">produk dalam katalog</span>
                </section>

                <section class="products-panel" aria-labelledby="products-heading">
                    <div class="panel-heading">
                        <div>
                            <h2 id="products-heading">Semua produk</h2>
                            <p>Kelola informasi produk dan ketersediaan stok.</p>
                        </div>
                        <label class="search-box">
                            <span aria-hidden="true">⌕</span>
                            <input id="product-search" type="search" placeholder="Cari produk…" aria-label="Cari produk" disabled>
                        </label>
                    </div>

                    <div class="notice notice-error" id="page-error" role="alert" hidden></div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">PRODUK</th>
                                    <th scope="col">KATEGORI</th>
                                    <th scope="col">HARGA</th>
                                    <th scope="col">STOK</th>
                                    <th scope="col"><span class="sr-only">Aksi</span></th>
                                </tr>
                            </thead>
                            <tbody id="product-list">
                                <tr><td class="table-message" colspan="5">Memeriksa koneksi API sebelum memuat produk…</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <span id="table-caption">Menunggu koneksi API</span>
                        <span>Data diperbarui langsung dari API</span>
                    </div>
                </section>
                <p class="page-footnote">CRUD Demo <span>·</span> Laravel REST API</p>
            </div>
        </section>
    </main>

    <dialog class="product-dialog" id="product-dialog" aria-labelledby="dialog-title">
        <form id="product-form">
            <div class="dialog-heading">
                <div>
                    <p class="eyebrow">KATALOG PRODUK</p>
                    <h2 id="dialog-title">Tambah produk</h2>
                    <p>Isi detail produk di bawah ini.</p>
                </div>
                <button class="icon-button dialog-close" id="close-dialog" type="button" aria-label="Tutup">×</button>
            </div>

            <div class="form-error notice notice-error" id="form-error" role="alert" hidden></div>
            <input id="product-id" type="hidden">
            <label class="field-label" for="product-name">Nama produk <span>*</span></label>
            <input class="form-input" id="product-name" name="name" maxlength="120" placeholder="Contoh: Kopi Arabika" required>
            <div class="form-row">
                <div>
                    <label class="field-label" for="product-category">Kategori <span>*</span></label>
                    <input class="form-input" id="product-category" name="category" maxlength="80" placeholder="Contoh: Minuman" required>
                </div>
                <div>
                    <label class="field-label" for="product-price">Harga (Rp) <span>*</span></label>
                    <input class="form-input" id="product-price" name="price" type="number" min="0" step="100" placeholder="25000" required>
                </div>
            </div>
            <label class="field-label" for="product-stock">Stok <span>*</span></label>
            <input class="form-input" id="product-stock" name="stock" type="number" min="0" step="1" placeholder="24" required>
            <label class="field-label" for="product-description">Deskripsi <span class="optional">(opsional)</span></label>
            <textarea class="form-input form-textarea" id="product-description" name="description" maxlength="1000" rows="3" placeholder="Catatan singkat tentang produk"></textarea>
            <div class="dialog-actions">
                <button class="button button-secondary" id="cancel-dialog" type="button">Batal</button>
                <button class="button button-primary" id="save-product" type="submit">Simpan produk</button>
            </div>
        </form>
    </dialog>

    <div class="toast" id="toast" role="status" aria-live="polite" hidden></div>
</body>
</html>
