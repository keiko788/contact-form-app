<?php

namespace Tests\Unit;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactValidationTest extends TestCase
{
    use RefreshDatabase;

    private array $validData;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $this->validData = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
            'tag_ids' => [$tag->id],
        ];
    }

    private function validator(array $data)
    {
        $request = new StoreContactRequest;

        return Validator::make($data, $request->rules());
    }

    /** @test */
    public function 電話番号形式が不正の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData;
        $data['tel'] = '090-1234-5678';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('tel'));
    }

    /** @test */
    public function 姓が未入力の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData;
        $data['first_name'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('first_name'));
    }

    /** @test */
    public function 名が未入力の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData;
        $data['last_name'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('last_name'));
    }

    /** @test */
    public function 性別が不正な値の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData;
        $data['gender'] = 0;

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('gender'));
    }

    /** @test */
    public function メールアドレス形式が不正な値の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData;
        $data['email'] = 'testcom';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('email'));
    }

    /** @test */
    public function お問い合わせの種類が未入力の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData;
        $data['category_id'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('category_id'));
    }

    /** @test */
    public function お問い合わせの内容が未入力の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData;
        $data['detail'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('detail'));
    }

    /** @test */
    public function 全ての必須項目とタグ入力を受け付ける(): void
    {
        $data = $this->validData;

        $validator = $this->validator($data);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 建物名が未入力でも受け付ける(): void
    {
        $data = $this->validData;
        $data['building'] = '';

        $validator = $this->validator($data);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function タグが未選択でも受け付ける(): void
    {
        $data = $this->validData;
        $data['tag_ids'] = [];

        $validator = $this->validator($data);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 電話番号が10桁の場合は受け付ける(): void
    {
        $data = $this->validData;
        $data['tel'] = '0312345678';

        $validator = $this->validator($data);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 電話番号が11桁の場合は受け付ける(): void
    {
        $data = $this->validData;
        $data['tel'] = '03012345678';

        $validator = $this->validator($data);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 電話番号が9桁の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData;
        $data['tel'] = '012345678';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('tel'));
    }

    /** @test */
    public function 電話番号が12桁の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData;
        $data['tel'] = '030012345678';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('tel'));
    }

    /** @test */
    public function お問い合わせ内容が120文字なら受け付ける(): void
    {
        $data = $this->validData;
        $data['detail'] = str_repeat('あ', 120);

        $validator = $this->validator($data);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function お問い合わせ内容が121文字の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData;
        $data['detail'] = str_repeat('あ', 121);

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('detail'));
    }
}
