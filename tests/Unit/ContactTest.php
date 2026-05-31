<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせから紐づくカテゴリを取得できる(): void
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertEquals(
            $category->id,
            $contact->category_id
        );
    }

    /** @test */
    public function お問い合わせから紐づく複数のタグを取得できる(): void
    {
        $category = Category::factory()->create();

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach([
            $tag1->id,
            $tag2->id,
        ]);

        $this->assertCount(2, $contact->tags);

        $this->assertTrue($contact->tags->contains($tag1));
        $this->assertTrue($contact->tags->contains($tag2));
    }
}
