<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Package;

class PricingEngineService
{
    /**
     * Get the student's region (egypt, arab, foreign).
     */
    public function getStudentRegion(Student $student): string
    {
        if (!$student->country) {
            return 'foreign'; // Default fallback
        }
        return $student->country->region ?? 'foreign';
    }

    /**
     * Get the base USD price for a package based on the student's region.
     */
    public function getPackagePriceUsd(Package $package, Student $student): float
    {
        $region = $this->getStudentRegion($student);
        
        switch ($region) {
            case 'egypt':
                return (float) $package->egypt_price;
            case 'arab':
                return (float) $package->arab_price;
            case 'foreign':
            default:
                return (float) $package->foreign_price;
        }
    }

    /**
     * Get the teacher's hourly rate for a specific student's region.
     */
    public function getTeacherHourlyRate(Teacher $teacher, Student $student): float
    {
        $region = $this->getStudentRegion($student);
        
        $category = $teacher->category;
        
        if (!$category) {
            // Fallback to the old `salary` column if they don't have a category yet
            return (float) $teacher->salary;
        }

        switch ($region) {
            case 'egypt':
                return (float) $category->egypt_rate;
            case 'arab':
                return (float) $category->arab_rate;
            case 'foreign':
            default:
                return (float) $category->foreign_rate;
        }
    }

    /**
     * Calculate teacher earnings for a session and add it to their balance.
     */
    public function processSessionEarnings(Teacher $teacher, Student $student, int $durationMinutes): array
    {
        if ($durationMinutes <= 0) {
            return ['earnings' => 0.0, 'region' => 'foreign'];
        }

        $region = $this->getStudentRegion($student);
        $hourlyRate = $this->getTeacherHourlyRate($teacher, $student);
        $earnings = ($hourlyRate / 60) * $durationMinutes;
        
        // Add earnings to teacher's balance
        $teacher->balance += $earnings;
        $teacher->save();

        return [
            'earnings' => $earnings,
            'region'   => $region
        ];
    }
}
