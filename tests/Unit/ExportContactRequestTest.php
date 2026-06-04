<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validator(array $data)
    {
        $request = new ExportContactRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    /** @test */
    public function 正しいフィルタ条件は指定できる(): void
    {
        $category = Category::factory()->create();

        $validator = $this->validator([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-06-04',
        ]);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 不正な性別は指定できない(): void
    {
        $validator = $this->validator([
            'gender' => 999,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }

    /** @test */
    public function 存在しないカテゴリ_i_dは指定できない(): void
    {
        $validator = $this->validator([
            'category_id' => 9999,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }

    /** @test */
    public function 不正な日付形式は指定できない(): void
    {
        $validator = $this->validator([
            'date' => 'invalid-date',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
    }
}
