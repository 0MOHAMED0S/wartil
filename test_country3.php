<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(2);
$teacherProfile = $user->teacherProfile;
if (!$teacherProfile) {
    echo "No teacherProfile found for user 2\n";
} else {
    $application = $teacherProfile->application;
    if (!$application) {
        echo "No application found for teacherProfile\n";
    } else {
        $originCountryCode = $application->origin_country;
        echo "originCountryCode: " . ($originCountryCode ?? 'NULL') . "\n";
        if ($originCountryCode) {
            $country = \App\Models\Country::where('code', $originCountryCode)->first();
            if ($country) {
                echo "country code: " . $country->code . "\n";
                echo "country region: " . $country->region . "\n";
            } else {
                echo "No country found in DB for code: " . $originCountryCode . "\n";
            }
        }
    }
}
