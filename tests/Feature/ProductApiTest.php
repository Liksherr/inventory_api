<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_confirms_the_api_and_database_are_ready(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }

    public function test_health_endpoint_reports_when_the_product_migration_is_pending(): void
    {
        Schema::drop('products');

        $this->getJson('/api/health')
            ->assertServiceUnavailable()
            ->assertJsonPath('status', 'unavailable')
            ->assertJsonPath('message', 'Tabel produk belum tersedia. Jalankan php artisan migrate.');
    }

    public function test_products_can_be_created_listed_updated_and_deleted(): void
    {
        $attributes = [
            'name' => 'Kopi Arabika',
            'category' => 'Minuman',
            'price' => 45000,
            'stock' => 12,
            'description' => 'Kopi pilihan.',
        ];

        $created = $this->postJson('/api/products', $attributes)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Kopi Arabika');

        $productId = $created->json('data.id');

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->putJson("/api/products/{$productId}", [
            ...$attributes,
            'stock' => 4,
        ])->assertOk()
            ->assertJsonPath('data.stock', 4);

        $this->deleteJson("/api/products/{$productId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('products', ['id' => $productId]);
    }

    public function test_product_api_validates_required_and_non_negative_fields(): void
    {
        $this->postJson('/api/products', [
            'name' => '',
            'category' => 'Minuman',
            'price' => -1,
            'stock' => -2,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'stock']);
    }

    public function test_show_endpoint_returns_a_product(): void
    {
        $product = Product::query()->create([
            'name' => 'Teh Hijau',
            'category' => 'Minuman',
            'price' => 12000,
            'stock' => 8,
        ]);

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Teh Hijau');
    }
}
