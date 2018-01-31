<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\updateProduct',
        'App\Console\Commands\releasePins',
        'App\Console\Commands\updatePins',
        'App\Console\Commands\repinPins',
        'App\Console\Commands\updateBoards',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('update:product')->twiceDaily(1, 13)->runInBackground();
        $schedule->command('update:boards')->twiceDaily(2, 14)->runInBackground();
        $schedule->command('release:pins')->hourly()->runInBackground();
        $schedule->command('update:pins')->hourly()->runInBackground();
        $schedule->command('repin:pins')->dailyAt('21:00')->runInBackground();
        $schedule->exec('curl https://www.google.com/webmasters/tools/ping?sitemap=http%3a%2f%2fpins.jeulia.com%2fupload%2fsitemap.xml')->twiceDaily(2, 14);
        $schedule->exec('curl https://www.bing.com/ping?siteMap=http%3a%2f%2fpins.jeulia.com%2fupload%2fsitemap.xml')->twiceDaily(2, 14);
    }
}
