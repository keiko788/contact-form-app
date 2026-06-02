<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者はお問い合わせ一覧画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    /** @test */
    public function 管理者はキーワードでお問い合わせを検索できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'category_id' => $category->id,
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'satou@example.com',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?keyword=山田');

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    /** @test */
    public function 管理者は性別でお問い合わせを検索できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'category_id' => $category->id,
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?gender=1');

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    /** @test */
    public function 管理者はカテゴリでお問い合わせを検索できる(): void
    {
        $user = User::factory()->create();
        $category1 = Category::factory()->create([
            'content' => 'カテゴリ1',
        ]);
        $category2 = Category::factory()->create([
            'content' => 'カテゴリ2',
        ]);

        Contact::factory()->create([
            'first_name' => '山田',
            'category_id' => $category1->id,
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'category_id' => $category2->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?category_id='.$category1->id);

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    /** @test */
    public function 管理者は日付でお問い合わせを検索できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Contact::factory()->create([
            'first_name' => '山田',
            'created_at' => '2024-01-01',
            'category_id' => $category->id,
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'created_at' => '2024-01-02',
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?date=2024-01-01');

        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    /** @test */
    public function 管理者はお問い合わせ詳細をカテゴリ情報付きで表示できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'content' => 'テストカテゴリ',
        ]);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/contacts/'.$contact->id);

        $response->assertStatus(200);
        $response->assertSee('テストカテゴリ');
    }

    /** @test */
    public function 管理者はお問い合わせを削除できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
        ]);

        $response = $this->actingAs($user)
            ->delete('/admin/contacts/'.$contact->id);

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    /** @test */
    public function 管理者はお問い合わせ一覧を7件ごとのページネーションで表示できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Contact::factory()->count(8)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);

        $response->assertViewHas(
            'contacts',
            fn ($contacts) => $contacts->total() === 8
                && $contacts->perPage() === 7
                && $contacts->lastPage() === 2
        );
    }

    /** @test */
    public function 管理者はタグを作成できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/admin/tags', [
                'name' => 'テストタグ',
            ]);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function 管理者はタグを編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create([
            'name' => 'テストタグ',
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/tags/'.$tag->id.'/edit');

        $response->assertStatus(200);
        $response->assertViewHas('tag', $tag);
    }

    /** @test */
    public function 管理者はタグを更新できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create([
            'name' => '更新前タグ',
        ]);

        $response = $this->actingAs($user)
            ->put('/admin/tags/'.$tag->id, [
                'name' => '更新後タグ',
            ]);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', [
            'name' => '更新後タグ',
        ]);
    }

    /** @test */
    public function 管理者はタグを削除できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)
            ->delete('/admin/tags/'.$tag->id);

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    /** @test */
    public function 未認証ユーザーはタグ操作が拒否される(): void
    {
        $response = $this->post('/admin/tags', [
            'name' => 'テストタグ',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('tags', [
            'name' => 'テストタグ',
        ]);
    }
}
