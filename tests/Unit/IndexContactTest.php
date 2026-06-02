<?php

namespace Tests\Unit;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせ検索で不正な性別値を指定できない(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['gender' => 5],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'gender',
            $validator->errors()->toArray()
        );
    }

    /** @test */
    public function お問い合わせ検索で性別に有効な値を指定できる(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['gender' => 1],
            $request->rules()
        );
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function お問い合わせ検索で存在しないカテゴリ_i_dは指定できない(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['category_id' => 999],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'category_id',
            $validator->errors()->toArray()
        );
    }

    /** @test */
    public function お問い合わせ検索で存在するカテゴリ_i_dを指定できる(): void
    {
        $request = new IndexContactRequest;

        $category = Category::factory()->create();

        $validator = Validator::make(
            ['category_id' => $category->id],
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function お問い合わせ検索で不正な日付形式はエラーになる(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make(
            ['date' => 'abc'],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'date',
            $validator->errors()->toArray()
        );
    }

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
}
