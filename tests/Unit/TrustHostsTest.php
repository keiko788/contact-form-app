<?php

namespace Tests\Unit;

use App\Http\Middleware\TrustHosts;
use Tests\TestCase;

class TrustHostsTest extends TestCase
{
    /** @test */
    public function 信頼するホストの配列を取得できる(): void
    {
        $middleware = new TrustHosts($this->app);

        $this->assertIsArray($middleware->hosts());
    }
}
