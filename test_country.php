<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(2);
$teacherProfile = $user->teacherProfile;
$application = $teacherProfile->application;
$originCountryCode = $application->origin_country;
$country = \App\Models\Country::where('code', $originCountryCode)->first();
var_dump($originCountryCode);
var_dump($country ? $country->region : "NOT FOUND");
