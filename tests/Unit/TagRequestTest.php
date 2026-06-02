<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TagRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function タグ作成時タグ名は必須である(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make(
            ['name' => ''],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    /** @test */
    public function タグ作成時タグ名は50文字以内で登録できる(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make(
            ['name' => str_repeat('あ', 50)],
            $request->rules()
        );
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function タグ作成時タグ名は51文字以上でバリデーションエラーになる(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make(
            ['name' => str_repeat('あ', 51)],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    /** @test */
    public function タグ作成時重複したタグ名は登録できない(): void
    {
        Tag::create([
            'name' => 'テストタグ',
        ]);

        $request = new StoreTagRequest;

        $validator = Validator::make(
            ['name' => 'テストタグ'],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    /** @test */
    public function 更新時に有効なタグ名を指定できる(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $request = new UpdateTagRequest;
        $request->tag = $tag;

        $validator = Validator::make(
            ['name' => '更新タグ'],
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 更新時に無効なタグ名を指定するとバリデーションエラーになる(): void
    {
        Tag::create([
            'name' => 'テストタグ',
        ]);

        $request = new UpdateTagRequest;

        $validator = Validator::make(
            ['name' => ''],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    /** @test */
    public function 更新時に自分自身のタグ名は指定できる(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $request = new UpdateTagRequest;
        $request->tag = $tag;

        $validator = Validator::make(
            ['name' => 'テストタグ'],
            $request->rules()
        );
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 更新時に重複したタグ名は指定できない(): void
    {
        $currentTag = Tag::create([
            'name' => '現在のタグ',
        ]);

        Tag::create([
            'name' => 'テストタグ',
        ]);

        $request = new UpdateTagRequest;
        $request->tag = $currentTag;

        $validator = Validator::make(
            ['name' => 'テストタグ'],
            $request->rules()
        );
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }
}
