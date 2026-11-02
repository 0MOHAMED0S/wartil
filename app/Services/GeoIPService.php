<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeoIPService
{
    public static function getCountryCode($ip = null)
    {
        try {
            // 1. إذا كان الموقع خلف Cloudflare، خذ كود الدولة فوراً (الأسرع والأدق 100%)
            if ($cfCountry = request()->header('CF-IPCountry')) {
                return strtoupper(trim($cfCountry));
            }

            // 2. جلب الـ IP الحقيقي للمستخدم حتى لو كان خلف Proxy
            $ip = request()->header('CF-Connecting-IP') 
               ?? request()->header('X-Forwarded-For') 
               ?? $ip 
               ?? request()->ip();

            // إذا كان هناك أكثر من IP في X-Forwarded-For نأخذ الأول
            if (str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }

            // إذا كان الـ IP محلياً (Localhost / Private IP) نرجع EG كقيمة افتراضية للتجربة
            if (in_array($ip, ['127.0.0.1', '::1']) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
                return 'EG';
            }

            // 3. الفحص عبر ip-api.com
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,countryCode");
            if ($response->successful() && $response->json('status') === 'success') {
                return $response->json('countryCode');
            }

            // 4. خدمة بديلة (Fallback) تدعم HTTPS مجاناً في حال فشل الأولى
            $fallback = Http::timeout(3)->get("https://ipwho.is/{$ip}");
            if ($fallback->successful() && $fallback->json('success') === true) {
                return $fallback->json('country_code');
            }

        } catch (\Throwable $e) {
            // تجاهل الأخطاء لعدم تعطيل الـ API
        }

        return 'EGj'; // القيمة الافتراضية في حال تعذر معرفة الدولة
    }
}