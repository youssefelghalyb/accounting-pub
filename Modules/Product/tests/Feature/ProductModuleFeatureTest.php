<?php

namespace Modules\Product\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Product;
use Modules\Product\Models\Book;
use Modules\Product\Models\Author;
use Modules\Product\Models\BookCategory;
use App\Models\User;

class ProductModuleFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_a_complete_book_with_product()
    {
        $author = Author::create([
            'full_name' => 'John Doe',
            'nationality' => 'American',
            'email' => 'john@example.com',
        ]);

        $category = BookCategory::create(['name' => 'Fiction']);
        $subCategory = BookCategory::create([
            'name' => 'Mystery',
            'parent_id' => $category->id,
        ]);

        $product = Product::create([
            'name' => 'The Great Mystery',
            'type' => 'book',
            'sku' => 'BK-001',
            'description' => 'A thrilling mystery novel',
            'base_price' => 29.99,
            'status' => 'active',
        ]);

        $book = Book::create([
            'product_id' => $product->id,
            'author_id' => $author->id,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'isbn' => '978-1234567890',
            'num_of_pages' => 350,
            'cover_type' => 'hard',
            'published_at' => '2025-01-15',
            'language' => 'English',
            'is_translated' => false,
        ]);

        // Assertions
        $this->assertDatabaseHas('products', [
            'name' => 'The Great Mystery',
            'type' => 'book',
            'base_price' => 29.99,
        ]);

        $this->assertDatabaseHas('books', [
            'product_id' => $product->id,
            'author_id' => $author->id,
            'isbn' => '978-1234567890',
        ]);

        // Test relationships
        $this->assertEquals($product->id, $book->product->id);
        $this->assertEquals($author->id, $book->author->id);
        $this->assertEquals($category->id, $book->category->id);
        $this->assertEquals($subCategory->id, $book->subCategory->id);
        $this->assertEquals('Fiction > Mystery', $book->subCategory->full_name);
    }

    /** @test */
    public function it_can_create_translated_book()
    {
        $author = Author::create(['full_name' => 'Ahmed Ali']);
        $product = Product::create(['name' => 'Arabic Classic', 'type' => 'book']);

        $book = Book::create([
            'product_id' => $product->id,
            'author_id' => $author->id,
            'isbn' => '978-1234567890',
            'is_translated' => true,
            'translated_from' => 'Arabic',
            'translated_to' => 'English',
            'translator_name' => 'Jane Smith',
        ]);

        $this->assertTrue($book->is_translated);
        $this->assertStringContainsString('Arabic', $book->translation_info);
        $this->assertStringContainsString('English', $book->translation_info);
        $this->assertStringContainsString('Jane Smith', $book->translation_info);
    }

    /** @test */
    public function it_cascades_delete_from_product_to_book()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $book = Book::create([
            'product_id' => $product->id,
            'isbn' => '978-1234567890',
        ]);

        $bookId = $book->id;

        $product->delete();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('books', ['id' => $bookId]);
    }

    /** @test */
    public function it_can_query_books_by_category()
    {
        $fiction = BookCategory::create(['name' => 'Fiction']);
        $mystery = BookCategory::create(['name' => 'Mystery', 'parent_id' => $fiction->id]);
        $scifi = BookCategory::create(['name' => 'Science Fiction']);

        $product1 = Product::create(['name' => 'Mystery Book', 'type' => 'book']);
        $product2 = Product::create(['name' => 'SciFi Book', 'type' => 'book']);

        Book::create([
            'product_id' => $product1->id,
            'category_id' => $fiction->id,
            'sub_category_id' => $mystery->id,
            'isbn' => '978-1234567890',
        ]);

        Book::create([
            'product_id' => $product2->id,
            'category_id' => $scifi->id,
            'isbn' => '978-1234567891',
        ]);

        $fictionBooks = $fiction->books;
        $this->assertCount(1, $fictionBooks);

        $mysterySubCategoryBooks = $mystery->subCategoryBooks;
        $this->assertCount(1, $mysterySubCategoryBooks);
    }

    /** @test */
    public function it_can_query_all_books_by_author()
    {
        $author = Author::create(['full_name' => 'Prolific Writer']);

        $product1 = Product::create(['name' => 'First Book', 'type' => 'book']);
        $product2 = Product::create(['name' => 'Second Book', 'type' => 'book']);
        $product3 = Product::create(['name' => 'Third Book', 'type' => 'book']);

        Book::create(['product_id' => $product1->id, 'author_id' => $author->id, 'isbn' => '978-1234567890']);
        Book::create(['product_id' => $product2->id, 'author_id' => $author->id, 'isbn' => '978-1234567891']);
        Book::create(['product_id' => $product3->id, 'author_id' => $author->id, 'isbn' => '978-1234567892']);

        $this->assertCount(3, $author->books);
    }

    /** @test */
    public function it_filters_products_by_type_and_status()
    {
        Product::create(['name' => 'Active Book', 'type' => 'book', 'status' => 'active']);
        Product::create(['name' => 'Active Ebook', 'type' => 'ebook', 'status' => 'active']);
        Product::create(['name' => 'Inactive Book', 'type' => 'book', 'status' => 'inactive']);

        $activeBooks = Product::active()->byType('book')->get();

        $this->assertCount(1, $activeBooks);
        $this->assertEquals('Active Book', $activeBooks->first()->name);
    }

    /** @test */
    public function it_tracks_product_creator_and_editor()
    {
        $creator = User::factory()->create(['name' => 'Creator User']);
        $editor = User::factory()->create(['name' => 'Editor User']);

        $product = Product::create([
            'name' => 'Test Product',
            'type' => 'book',
            'created_by' => $creator->id,
            'edited_by' => $editor->id,
        ]);

        $this->assertEquals('Creator User', $product->creator->name);
        $this->assertEquals('Editor User', $product->editor->name);
    }

    /** @test */
    public function category_can_have_hierarchical_structure()
    {
        $parent = BookCategory::create(['name' => 'Literature']);
        $child1 = BookCategory::create(['name' => 'Poetry', 'parent_id' => $parent->id]);
        $child2 = BookCategory::create(['name' => 'Prose', 'parent_id' => $parent->id]);

        $this->assertTrue($parent->is_parent);
        $this->assertFalse($child1->is_parent);
        $this->assertCount(2, $parent->children);
        $this->assertEquals('Literature > Poetry', $child1->full_name);
    }

    /** @test */
    public function it_can_scope_parent_and_child_categories()
    {
        $parent1 = BookCategory::create(['name' => 'Fiction']);
        $parent2 = BookCategory::create(['name' => 'Non-Fiction']);
        $child1 = BookCategory::create(['name' => 'Mystery', 'parent_id' => $parent1->id]);
        $child2 = BookCategory::create(['name' => 'Biography', 'parent_id' => $parent2->id]);

        $parents = BookCategory::parents()->get();
        $children = BookCategory::children()->get();

        $this->assertCount(2, $parents);
        $this->assertCount(2, $children);
        $this->assertTrue($parents->contains('name', 'Fiction'));
        $this->assertTrue($children->contains('name', 'Mystery'));
    }

    /** @test */
    public function it_handles_null_author_for_books()
    {
        $product = Product::create(['name' => 'Anonymous Work', 'type' => 'book']);
        $book = Book::create([
            'product_id' => $product->id,
            'author_id' => null,
            'isbn' => '978-1234567890',
        ]);

        $this->assertNull($book->author);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'author_id' => null,
        ]);
    }

    /** @test */
    public function it_provides_cover_type_colors()
    {
        $product1 = Product::create(['name' => 'Hardcover Book', 'type' => 'book']);
        $product2 = Product::create(['name' => 'Softcover Book', 'type' => 'book']);

        $hardcover = Book::create([
            'product_id' => $product1->id,
            'isbn' => '978-1234567890',
            'cover_type' => 'hard',
        ]);

        $softcover = Book::create([
            'product_id' => $product2->id,
            'isbn' => '978-1234567891',
            'cover_type' => 'soft',
        ]);

        $this->assertEquals('blue', $hardcover->cover_type_color);
        $this->assertEquals('green', $softcover->cover_type_color);
    }
}
