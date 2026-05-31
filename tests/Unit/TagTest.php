<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function タグから複数のお問い合わせを取得できる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $contact1 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact2 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $tag->contacts()->attach([
            $contact1->id,
            $contact2->id,
        ]);

        $this->assertCount(2, $tag->contacts);

        $this->assertTrue($tag->contacts->contains($contact1));
        $this->assertTrue($tag->contacts->contains($contact2));
    }
}
