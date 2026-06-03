<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせ一覧を_jso_n形式で取得できる(): void
    {
        $category = Category::factory()->create();
        Contact::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                    'gender',
                    'email',
                    'tel',
                    'address',
                    'building',
                    'detail',
                    'category' => [
                        'id',
                        'content',
                    ],
                    'tags',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);
    }

    /** @test */
    public function お問い合わせ一覧をキーワードで検索できる(): void
    {
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'email' => 'yamada@example.com',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '佐藤',
            'email' => 'sato@example.com',
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=山田');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'first_name' => '山田',
        ]);
    }

    /** @test */
    public function お問い合わせ一覧を日付で検索できる(): void
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => '2026-06-03 10:00:00',
        ]);

        $response = $this->getJson('/api/v1/contacts?date=2026-06-03');

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $contact->id,
        ]);
    }

    /** @test */
    public function お問い合わせ一覧を性別で検索できる(): void
    {
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'gender' => 2,
        ]);

        $response = $this->getJson('/api/v1/contacts?gender=1');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function お問い合わせ一覧をカテゴリーで検索できる(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category1->id,
        ]);

        Contact::factory()->create([
            'category_id' => $category2->id,
        ]);

        $response = $this->getJson("/api/v1/contacts?category_id={$category1->id}");

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function お問い合わせ一覧をページネーションできる(): void
    {
        $category = Category::factory()->create();

        Contact::factory()->count(25)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?per_page=10');

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta/per_page', 10);
        $response->assertJsonPath('meta/total', 25);
    }

    public function お問い合わせ一覧で不正な性別を指定すると422が返る(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=0');

        $response->assertUnprocessable();
        $response->assertValidationErrors(['gender']);
        $response->assertJsonFragment([
            '性別の値が不正です',
        ]);
    }

    /** @test */
    public function お問い合わせ詳細を_jso_n形式で取得できる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach($tag->id);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'building',
                'detail',
                'category' => [
                    'id',
                    'content',
                ],
                'tags' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertJsonFragment([
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'content' => $category->content,
            'name' => $tag->name,
        ]);
    }

    /** @test */
    public function 存在しないお問い合わせ詳細で404エラーを返す(): void
    {
        $response = $this->getJson('/api/v1/contacts/9999');

        $response->assertNotFound();
    }

    /** @test */
    public function お問い合わせを作成できる(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
            'tag_ids' => [$tag->id],
        ];

        $response = $this->postJson(
            '/api/v1/contacts',
            $data
        );

        $response->assertCreated();

        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'tag_id' => $tag->id,
        ]);
    }

    /** @test */
    public function お問い合わせを作成時にバリデーションエラーで422が返る(): void
    {
        $response = $this->postJson('/api/v1/contacts', []);

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    /** @test */
    public function お問い合わせを更新できる(): void
    {
        $category = Category::factory()->create();
        $newCategory = Category::factory()->create();
        $tag = Tag::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '大阪府大阪市',
            'building' => '更新ビル',
            'category_id' => $newCategory->id,
            'detail' => '更新後のお問い合わせ',
            'tag_ids' => [$tag->id],
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertOk();

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => 'hanako@example.com',
            'first_name' => '佐藤',
        ]);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function 存在しないお問い合わせは更新できず404エラーが返る(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '大阪府大阪市',
            'building' => '更新ビル',
            'category_id' => $category->id,
            'detail' => '更新後のお問い合わせ',
            'tag_ids' => [],
        ];

        $response = $this->putJson('/api/v1/contacts/9999', $data);

        $response->assertNotFound();

        $response->assertJson([
            'error' => 'お問い合わせが見つかりませんでした。',
        ]);
    }

    public function お問い合わせ更新で存在しないカテゴリーを指定すると422エラーが返る(): void
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $data = [
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '大阪府大阪市',
            'building' => '更新ビル',
            'category_id' => 9999,
            'detail' => '更新後のお問い合わせ',
            'tag_ids' => [],
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertUnprocessable();
        $response->assertValidationErrors(['caategory_id']);
        $response->assertJson([
            '選択されたカテゴリーが存在しません',
        ]);
    }

    /** @test */
    public function お問い合わせを削除できる(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
        ]);

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function 存在しないお問い合わせは削除できず404エラーが返る(): void
    {
        $category = Category::factory()->create();

        $data = [
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '大阪府大阪市',
            'building' => '更新ビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ',
            'tag_ids' => [],
        ];

        $response = $this->deleteJson('/api/v1/contacts/9999', $data);

        $response->assertNotFound();

        $response->assertJson([
            'error' => 'お問い合わせが見つかりませんでした。',
        ]);
    }
}
