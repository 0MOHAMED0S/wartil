<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = App\Models\Teacher::find(1);
if ($teacher) {
    echo "Found teacher 1. user_id: " . $teacher->user_id . "\n";
    $application = $teacher->application;
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
} else {
    echo "No teacher found for id 1\n";
}
