<?php

namespace Modules\Product\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Product;
use Modules\Product\Models\Book;
use App\Models\User;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_a_product()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'type' => 'book',
            'sku' => 'TEST-001',
            'description' => 'Test description',
            'base_price' => 29.99,
            'status' => 'active',
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('book', $product->type);
        $this->assertEquals(29.99, $product->base_price);
    }

    /** @test */
    public function it_casts_base_price_to_decimal()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'type' => 'book',
            'base_price' => 29.999,
        ]);

        $this->assertEquals('29.99', number_format($product->base_price, 2));
    }

    /** @test */
    public function it_has_one_book_relationship()
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $product->book());
    }

    /** @test */
    public function it_belongs_to_creator_user()
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Test Product',
            'type' => 'book',
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $product->creator);
        $this->assertEquals($user->id, $product->creator->id);
    }

    /** @test */
    public function it_belongs_to_editor_user()
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Test Product',
            'type' => 'book',
            'edited_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $product->editor);
        $this->assertEquals($user->id, $product->editor->id);
    }

    /** @test */
    public function it_can_scope_active_products()
    {
        Product::create(['name' => 'Active Product', 'type' => 'book', 'status' => 'active']);
        Product::create(['name' => 'Inactive Product', 'type' => 'book', 'status' => 'inactive']);

        $activeProducts = Product::active()->get();

        $this->assertCount(1, $activeProducts);
        $this->assertEquals('Active Product', $activeProducts->first()->name);
    }

    /** @test */
    public function it_can_scope_by_type()
    {
        Product::create(['name' => 'Book Product', 'type' => 'book']);
        Product::create(['name' => 'Ebook Product', 'type' => 'ebook']);
        Product::create(['name' => 'Journal Product', 'type' => 'journal']);

        $books = Product::byType('book')->get();

        $this->assertCount(1, $books);
        $this->assertEquals('Book Product', $books->first()->name);
    }

    /** @test */
    public function it_returns_status_color_attribute()
    {
        $activeProduct = Product::create(['name' => 'Active', 'type' => 'book', 'status' => 'active']);
        $inactiveProduct = Product::create(['name' => 'Inactive', 'type' => 'book', 'status' => 'inactive']);

        $this->assertEquals('green', $activeProduct->status_color);
        $this->assertEquals('gray', $inactiveProduct->status_color);
    }

    /** @test */
    public function it_returns_type_color_attribute()
    {
        $book = Product::create(['name' => 'Book', 'type' => 'book']);
        $ebook = Product::create(['name' => 'Ebook', 'type' => 'ebook']);
        $journal = Product::create(['name' => 'Journal', 'type' => 'journal']);

        $this->assertEquals('blue', $book->type_color);
        $this->assertEquals('purple', $ebook->type_color);
        $this->assertEquals('green', $journal->type_color);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'name', 'type', 'sku', 'description', 'base_price',
            'status', 'created_by', 'edited_by'
        ];

        $product = new Product();

        $this->assertEquals($fillable, $product->getFillable());
    }
}
