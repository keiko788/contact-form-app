<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function カテゴリから紐づく複数のお問い合わせを取得できる(): void
    {
        $category = Category::factory()->create();

        $contact1 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);
        $contact2 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertCount(2, $category->contacts);

        $this->assertTrue(
            $category->contacts->contains($contact1)
        );
        $this->assertTrue(
            $category->contacts->contains($contact2)
        );
    }
}
