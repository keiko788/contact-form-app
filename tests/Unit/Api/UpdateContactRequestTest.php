<?php

namespace Tests\Unit\Api;

use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateContactRequestTest extends TestCase
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
        $request = new UpdateContactRequest;

        return Validator::make($data, $request->rules());
    }

    /** @test */
    public function 正しい入力データはバリデーションを通過する()
    {
        $data = $this->validData;

        $validator = $this->validator($data);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 姓が未入力の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['first_name'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 名が未入力の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['last_name'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 性別が未入力の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['gender'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 性別が不正な値の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['gender'] = '5';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function メールアドレスが未入力の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['email'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function メールアドレスが不正な値の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['email'] = 'testcom';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 電話番号が未入力の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['tel'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 電話番号が9桁の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['tel'] = '090123456';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 電話番号が10桁の場合は指定できる()
    {
        $data = $this->validData;
        $data['tel'] = '0901234567';

        $validator = $this->validator($data);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 電話番号が11桁の場合は指定できる()
    {
        $data = $this->validData;
        $data['tel'] = '09012345678';

        $validator = $this->validator($data);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 電話番号に数字以外の値を含む場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['tel'] = '090-1234-5678';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 住所が未入力の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['address'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function カテゴリーが未入力の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['category_id'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 存在しないカテゴリーを指定した場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['category_id'] = 9999;

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function 存在しないタグを指定した場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['tag_ids'] = [9999];

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tag_ids.0', $validator->errors()->toArray());
    }

    /** @test */
    public function お問い合わせ内容が未入力の場合はバリデーションエラーになる()
    {
        $data = $this->validData;
        $data['detail'] = '';

        $validator = $this->validator($data);

        $this->assertTrue($validator->fails());
    }
}
