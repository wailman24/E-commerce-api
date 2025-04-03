<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Otp;
use Carbon\Carbon;

class ClearExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-expired-otps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired OTPs from the database';

    /**
     * Execute the console command.
     */
    public function handle() {}

    /*  public function schedule(\Illuminate\Console\Scheduling\Schedule $schedule)
    {
        $schedule->everyMinute(); // Run every hour
    } */
}
