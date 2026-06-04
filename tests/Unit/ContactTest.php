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

    /** @test */
    public function 性別が1の場合は男性を返す(): void
    {
        $contact = new Contact([
            'gender' => 1,
        ]);

        $this->assertSame(
            '男性',
            $contact->gender_label
        );
    }

    /** @test */
    public function 性別が2の場合は女性を返す(): void
    {
        $contact = new Contact([
            'gender' => 2,
        ]);

        $this->assertSame(
            '女性',
            $contact->gender_label
        );
    }

    /** @test */
    public function 性別が3の場合はその他を返す(): void
    {
        $contact = new Contact([
            'gender' => 3,
        ]);

        $this->assertSame(
            'その他',
            $contact->gender_label
        );
    }

    /** @test */
    public function 性別が不正な値の場合は不明を返す(): void
    {
        $contact = new Contact([
            'gender' => 999,
        ]);

        $this->assertSame(
            '不明',
            $contact->gender_label
        );
    }
}
