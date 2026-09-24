<?php

namespace App\Services;

class HotspotDataValueService
{
    public static function calculate(int $totalBytes): int
    {
        if ($totalBytes <= 0) {
            return 0;
        }

        $totalMb = $totalBytes / 1048576;

        if ($totalMb <= 246) {
            $pricePerMb = 500 / 246;
        } elseif ($totalMb <= 492) {
            $pricePerMb = 1000 / 492;
        } elseif ($totalMb <= 985) {
            $pricePerMb = 2000 / 985;
        } elseif ($totalMb <= 1024) {
            $pricePerMb = 2100 / 1024;
        } else {
            $pricePerMb = 3000 / (1.45 * 1024);
        }

        return (int) round($totalMb * $pricePerMb);
    }
}
