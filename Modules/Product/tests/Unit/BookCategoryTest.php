<?php

namespace Modules\Product\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\BookCategory;
use Modules\Product\Models\Book;
use App\Models\User;

class BookCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    /** @test */
    public function it_can_create_a_category()
    {
        $category = BookCategory::create([
            'name' => 'Fiction',
        ]);

        $this->assertInstanceOf(BookCategory::class, $category);
        $this->assertEquals('Fiction', $category->name);
    }

    /** @test */
    public function it_can_have_parent_category()
    {
        $parent = BookCategory::create(['name' => 'Fiction']);
        $child = BookCategory::create([
            'name' => 'Mystery',
            'parent_id' => $parent->id,
        ]);

        $this->assertInstanceOf(BookCategory::class, $child->parent);
        $this->assertEquals('Fiction', $child->parent->name);
    }

    /** @test */
    public function it_can_have_children_categories()
    {
        $parent = BookCategory::create(['name' => 'Fiction']);
        $child1 = BookCategory::create(['name' => 'Mystery', 'parent_id' => $parent->id]);
        $child2 = BookCategory::create(['name' => 'Romance', 'parent_id' => $parent->id]);

        $this->assertCount(2, $parent->children);
        $this->assertTrue($parent->children->contains('name', 'Mystery'));
        $this->assertTrue($parent->children->contains('name', 'Romance'));
    }

    /** @test */
    public function it_has_books_relationship()
    {
        $category = BookCategory::create(['name' => 'Fiction']);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $category->books());
    }

    /** @test */
    public function it_has_sub_category_books_relationship()
    {
        $category = BookCategory::create(['name' => 'Fiction']);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $category->subCategoryBooks());
    }

    /** @test */
    public function it_belongs_to_creator_user()
    {
        $user = User::factory()->create();
        $category = BookCategory::create([
            'name' => 'Fiction',
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $category->creator);
        $this->assertEquals($user->id, $category->creator->id);
    }

    /** @test */
    public function it_can_scope_parent_categories()
    {
        $parent = BookCategory::create(['name' => 'Fiction']);
        $child = BookCategory::create(['name' => 'Mystery', 'parent_id' => $parent->id]);

        $parents = BookCategory::parents()->get();

        $this->assertCount(1, $parents);
        $this->assertEquals('Fiction', $parents->first()->name);
    }

    /** @test */
    public function it_can_scope_children_categories()
    {
        $parent = BookCategory::create(['name' => 'Fiction']);
        $child = BookCategory::create(['name' => 'Mystery', 'parent_id' => $parent->id]);

        $children = BookCategory::children()->get();

        $this->assertCount(1, $children);
        $this->assertEquals('Mystery', $children->first()->name);
    }

    /** @test */
    public function it_returns_full_name_attribute_for_child()
    {
        $parent = BookCategory::create(['name' => 'Fiction']);
        $child = BookCategory::create(['name' => 'Mystery', 'parent_id' => $parent->id]);

        $this->assertEquals('Fiction > Mystery', $child->full_name);
    }

    /** @test */
    public function it_returns_full_name_attribute_for_parent()
    {
        $parent = BookCategory::create(['name' => 'Fiction']);

        $this->assertEquals('Fiction', $parent->full_name);
    }

    /** @test */
    public function it_returns_is_parent_attribute()
    {
        $parent = BookCategory::create(['name' => 'Fiction']);
        $child = BookCategory::create(['name' => 'Mystery', 'parent_id' => $parent->id]);

        $this->assertTrue($parent->is_parent);
        $this->assertFalse($child->is_parent);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = ['name', 'parent_id', 'created_by', 'edited_by'];

        $category = new BookCategory();

        $this->assertEquals($fillable, $category->getFillable());
    }
}
