<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvExportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者が性別検索結果を_cs_vでダウンロードできる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
        ]);

        $response = $this->actingAs($user)->get(
            '/contacts/export?gender=1'
        );

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('山田 太郎', $content);
        $this->assertStringNotContainsString('佐藤 花子', $content);
    }

    /** @test */
    public function 管理者がキーワード検索結果を_cs_vでダウンロードできる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'satou@example.com',
        ]);

        $response = $this->actingAs($user)->get(
            '/contacts/export?keyword=山田'
        );

        $response->assertOk();

        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('山田 太郎', $content);
        $this->assertStringNotContainsString('佐藤 花子', $content);
    }

    /** @test */
    public function 管理者がカテゴリー検索結果を_cs_vでダウンロードできる(): void
    {
        $user = User::factory()->create();

        $category1 = Category::factory()->create([
            'content' => 'テストカテゴリー１',
        ]);
        $category2 = Category::factory()->create([
            'content' => 'テストカテゴリー2',
        ]);

        Contact::factory()->create([
            'category_id' => $category1->id,
            'first_name' => '山田',
            'last_name' => '太郎',
        ]);

        Contact::factory()->create([
            'category_id' => $category2->id,
            'first_name' => '佐藤',
            'last_name' => '花子',
        ]);

        $response = $this->actingAs($user)->get(
            "/contacts/export?category_id={$category1->id}"
        );

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('山田 太郎', $content);
        $this->assertStringNotContainsString('佐藤 花子', $content);
    }

    /** @test */
    public function 管理者が日付検索結果を_cs_vでダウンロードできる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'email' => 'old@example.com',
            'created_at' => '2026-06-01 10:00:00',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'email' => 'new@example.com',
            'created_at' => '2026-06-04 10:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/contacts/export?date=2026-06-04');

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString(
            'new@example.com',
            $content
        );

        $this->assertStringNotContainsString(
            'old@example.com',
            $content
        );
    }

    /** @test */
    public function cs_vは無指定時に新着順で出力される(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '古',
            'last_name' => '太郎',
            'created_at' => now()->subDay(),
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '新',
            'last_name' => '花子',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(
            '/contacts/export'
        );

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertLessThan(
            mb_strpos($content, '古 太郎'),
            mb_strpos($content, '新 花子'),
        );
    }
}
