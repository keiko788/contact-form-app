<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private function validContactData(array $overrides = []): array
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        return array_merge([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル',
            'detail' => 'お問い合わせ内容',
            'tag_ids' => [$tag->id],
        ], $overrides);
    }

    /** @test */
    public function お問い合わせ入力ページが正常に表示される(): void
    {
        $response = $this->get(route('contact.index'));

        $response->assertOk();
        $response->assertViewIs('contact.index');
    }

    /** @test */
    public function お問い合わせ入力ページにカテゴリとタグがビュー変数として渡される(): void
    {
        Category::factory()->create();
        Tag::factory()->create();

        $response = $this->get(route('contact.index'));

        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
    }

    /** @test */
    public function お問い合わせ入力ページにカテゴリ名とタグ名が表示される(): void
    {
        Category::factory()->create([
            'content' => '商品について',
        ]);

        Tag::factory()->create([
            'name' => '重要',
        ]);

        $response = $this->get(route('contact.index'));

        $response->assertSee('商品について');
        $response->assertSee('重要');
    }

    /** @test */
    public function サンクスページが表示される(): void
    {
        $response = $this->get(route('contact.thanks'));

        $response->assertOk();
        $response->assertViewIs('contact.thanks');
    }

    /** @test */
    public function 確認画面が表示され、入力内容が表示される(): void
    {
        $category = Category::factory()->create([
            'content' => '商品について',
        ]);
        $tag = Tag::factory()->create([
            'name' => '重要',
        ]);

        $response = $this->post(route('contact.confirm'), [
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル',
            'detail' => 'お問い合わせ内容',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertOk();
        $response->assertViewIs('contact.confirm');
        $response->assertViewHas('category');
        $response->assertViewHas('tags');

        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('taro@example.com');
        $response->assertSee('商品について');
        $response->assertSee('重要');
    }

    /** @test */
    public function 必須項目未入力時はバリデーションエラーになる(): void
    {
        $response = $this->from(route('contact.index'))
            ->post(route('contact.confirm'), []);

        $response->assertRedirect(route('contact.index'));

        $response->assertSessionHasErrors([
            'category_id',
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'detail',
        ]);
    }

    /** @test */
    public function バリデーション通過時にお問い合わせが保存される(): void
    {
        $response = $this->post(
            route('contact.store'),
            $this->validContactData()
        );

        $this->assertDatabaseHas('contacts', [
            'email' => 'taro@example.com',
        ]);
    }

    /** @test */
    public function バリデーション通過時にタグが中間テーブルに保存される(): void
    {
        $this->post(
            route('contact.store'),
            $this->validContactData()
        );

        $contact = Contact::where('email', 'taro@example.com')->first();

        $tagId = $contact->tags->first()->id;

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tagId,
        ]);
    }

    /** @test */
    public function バリデーションエラー時はリダイレクトされエラーが返る(): void
    {
        $response = $this->from(route('contact.index'))
            ->post(route('contact.store'), []);

        $response->assertRedirect(route('contact.index'));

        $response->assertSessionHasErrors([
            'category_id',
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'detail',
        ]);
    }
}
