<?php

namespace Tests\Unit\Api;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせ検索でキーワードは255文字以内で指定できる(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['keyword' => str_repeat('あ', 255)],
            $request->rules()
        );
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function お問い合わせ検索でキーワードは256文字以上でバリデーションエラーになる(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['keyword' => str_repeat('あ', 256)],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'keyword',
            $validator->errors()->toArray()
        );
    }

    /** @test */
    public function お問い合わせ検索で不正な性別値は指定できない(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['gender' => 5],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());

        $this->assertEquals(
            '性別の値が不正です',
            $validator->errors()->first('gender')
        );
    }

    /** @test */
    public function お問い合わせ検索で正しい性別値を指定できる(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['gender' => 3],
            $request->rules(),
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function お問い合わせ検索で存在しないカテゴリーは指定できない(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['category_id' => 999],
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());

        $this->assertEquals(
            '選択されたカテゴリーが存在しません',
            $validator->errors()->first('category_id')
        );
    }

    /** @test */
    public function お問い合わせ検索で存在するカテゴリーを指定できる(): void
    {
        $category = Category::factory()->create();
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['category_id' => $category->id],
            $request->rules(),
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function お問い合わせ検索で不正な日付形式は指定できない(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['date' => 'abc'],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
    }

    /** @test */
    public function お問い合わせ検索でper_pageが101以上の値は指定できない(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['per_page' => 101],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }

    /** @test */
    public function お問い合わせ検索でper_pageが100以下なら指定できる(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['per_page' => 100],
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }
}
