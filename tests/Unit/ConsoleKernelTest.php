<?php

namespace Tests\Unit;

use App\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class ConsoleKernelTest extends TestCase
{
    /** @test */
    public function コンソールコマンドを登録できる(): void
    {
        $kernel = new class($this->app, $this->app['events']) extends Kernel
        {
            public function publicCommands(): void
            {
                $this->commands();
            }
        };

        $kernel->publicCommands();

        $this->assertTrue(true);
    }

    /** @test */
    public function スケジュール定義を実行できる(): void
    {
        $kernel = new class($this->app, $this->app['events']) extends Kernel
        {
            public function publicSchedule(Schedule $schedule): void
            {
                $this->schedule($schedule);
            }
        };

        $kernel->publicSchedule(new Schedule);

        $this->assertTrue(true);
    }
}
