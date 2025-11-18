<?php

namespace Modules\Product\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Book;
use Modules\Product\Models\Product;
use Modules\Product\Models\Author;
use Modules\Product\Models\BookCategory;
use Modules\Product\Models\Contract;
use App\Models\User;

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_a_book()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $author = Author::create(['full_name' => 'John Doe']);

        $book = Book::create([
            'product_id' => $product->id,
            'author_id' => $author->id,
            'isbn' => '978-1234567890',
            'num_of_pages' => 350,
            'cover_type' => 'soft',
        ]);

        $this->assertInstanceOf(Book::class, $book);
        $this->assertEquals('978-1234567890', $book->isbn);
        $this->assertEquals(350, $book->num_of_pages);
        $this->assertEquals('soft', $book->cover_type);
    }

    /** @test */
    public function it_belongs_to_product()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $book = Book::create([
            'product_id' => $product->id,
            'isbn' => '978-1234567890',
        ]);

        $this->assertInstanceOf(Product::class, $book->product);
        $this->assertEquals('Test Book', $book->product->name);
    }

    /** @test */
    public function it_belongs_to_author()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $author = Author::create(['full_name' => 'John Doe']);
        $book = Book::create([
            'product_id' => $product->id,
            'author_id' => $author->id,
            'isbn' => '978-1234567890',
        ]);

        $this->assertInstanceOf(Author::class, $book->author);
        $this->assertEquals('John Doe', $book->author->full_name);
    }

    /** @test */
    public function it_belongs_to_category()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $category = BookCategory::create(['name' => 'Fiction']);
        $book = Book::create([
            'product_id' => $product->id,
            'category_id' => $category->id,
            'isbn' => '978-1234567890',
        ]);

        $this->assertInstanceOf(BookCategory::class, $book->category);
        $this->assertEquals('Fiction', $book->category->name);
    }

    /** @test */
    public function it_belongs_to_sub_category()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $subCategory = BookCategory::create(['name' => 'Mystery']);
        $book = Book::create([
            'product_id' => $product->id,
            'sub_category_id' => $subCategory->id,
            'isbn' => '978-1234567890',
        ]);

        $this->assertInstanceOf(BookCategory::class, $book->subCategory);
        $this->assertEquals('Mystery', $book->subCategory->name);
    }

    /** @test */
    public function it_has_contracts_relationship()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $book = Book::create([
            'product_id' => $product->id,
            'isbn' => '978-1234567890',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $book->contracts());
    }

    /** @test */
    public function it_casts_published_at_to_date()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $book = Book::create([
            'product_id' => $product->id,
            'isbn' => '978-1234567890',
            'published_at' => '2025-11-18',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $book->published_at);
    }

    /** @test */
    public function it_casts_is_translated_to_boolean()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $book = Book::create([
            'product_id' => $product->id,
            'isbn' => '978-1234567890',
            'is_translated' => 1,
        ]);

        $this->assertTrue($book->is_translated);
        $this->assertIsBool($book->is_translated);
    }

    /** @test */
    public function it_returns_cover_type_color_attribute()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $hardCover = Book::create([
            'product_id' => $product->id,
            'isbn' => '978-1234567890',
            'cover_type' => 'hard',
        ]);
        $softCover = Book::create([
            'product_id' => $product->id,
            'isbn' => '978-1234567891',
            'cover_type' => 'soft',
        ]);

        $this->assertEquals('blue', $hardCover->cover_type_color);
        $this->assertEquals('green', $softCover->cover_type_color);
    }

    /** @test */
    public function it_returns_full_title_attribute()
    {
        $product = Product::create(['name' => 'Introduction to Laravel', 'type' => 'book']);
        $book = Book::create([
            'product_id' => $product->id,
            'isbn' => '978-1234567890',
        ]);

        $this->assertEquals('Introduction to Laravel', $book->full_title);
    }

    /** @test */
    public function it_returns_translation_info_attribute_when_translated()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $book = Book::create([
            'product_id' => $product->id,
            'isbn' => '978-1234567890',
            'is_translated' => true,
            'translated_from' => 'English',
            'translated_to' => 'Arabic',
            'translator_name' => 'Jane Smith',
        ]);

        $this->assertStringContainsString('From: English', $book->translation_info);
        $this->assertStringContainsString('To: Arabic', $book->translation_info);
        $this->assertStringContainsString('By: Jane Smith', $book->translation_info);
    }

    /** @test */
    public function it_returns_null_translation_info_when_not_translated()
    {
        $product = Product::create(['name' => 'Test Book', 'type' => 'book']);
        $book = Book::create([
            'product_id' => $product->id,
            'isbn' => '978-1234567890',
            'is_translated' => false,
        ]);

        $this->assertNull($book->translation_info);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'product_id', 'author_id', 'category_id', 'sub_category_id',
            'isbn', 'num_of_pages', 'cover_type', 'published_at', 'language',
            'is_translated', 'translated_from', 'translated_to', 'translator_name',
            'created_by', 'edited_by'
        ];

        $book = new Book();

        $this->assertEquals($fillable, $book->getFillable());
    }
}
