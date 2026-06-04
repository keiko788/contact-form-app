<?php

namespace Tests\Unit;

use App\Exceptions\Handler;
use App\Models\Contact;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    /** @test */
    public function apiリクエストのモデル未発見例外はカスタム404を返す(): void
    {
        $handler = new Handler($this->app);
        $handler->register();

        $exception = (new ModelNotFoundException)
            ->setModel(Contact::class, [9999]);

        $request = Request::create('/api/v1/contacts/9999', 'GET');

        $method = new ReflectionMethod($handler, 'renderViaCallbacks');
        $method->setAccessible(true);

        $response = $method->invoke($handler, $request, $exception);

        $this->assertSame(404, $response->getStatusCode());

        $this->assertSame(
            ['error' => 'お問い合わせが見つかりませんでした。'],
            $response->getData(true)
        );
    }
}
