<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/* Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote'); */

// ✅ Define the OTP clearing command
Artisan::command('otp:clear', function () {
    $deleted = DB::table('otps')->where('expires_at', '<', now())->delete();
    $this->info("Deleted $deleted expired OTP(s).");
})->purpose('Delete expired OTPs')->everySecond();
