<?php

namespace Tests\Unit;

use App\Providers\BroadcastServiceProvider;
use Tests\TestCase;

class BroadcastServiceProviderTest extends TestCase
{
    /** @test */
    public function ブロードキャストサービスプロバイダを起動できる(): void
    {
        $provider = new BroadcastServiceProvider($this->app);

        $provider->boot();

        $this->assertTrue(true);
    }
}
