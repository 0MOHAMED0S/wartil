<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$teacher = App\Models\Teacher::where('user_id', 2)->first();
if ($teacher) {
    $application = $teacher->application;
    if ($application) {
        $originCountryCode = $application->origin_country;
        $country = \App\Models\Country::where('code', $originCountryCode)->first();
        var_dump($originCountryCode);
        var_dump($country ? $country->region : "NOT FOUND");
        var_dump($country ? strtolower($country->region) : "NULL");
    } else {
        echo "No application\n";
    }
} else {
    echo "No teacher\n";
}
