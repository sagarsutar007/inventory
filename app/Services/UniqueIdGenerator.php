<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UniqueIdGenerator
{
    public static function generateId($table, $column, $prefix)
    {
        $year = Carbon::now()->format('y');
        $weekNumber = Carbon::now()->weekOfYear;
        $day = Carbon::now()->format('d');
        $idPrefix = $prefix . $year . $weekNumber . $day;

        // Retrieve the highest ID starting with the current year
        $lastId = DB::table($table)->where($column, 'like', '%' . $prefix . $year . '%')->max($column);

        if ($lastId) {
            // Extract the numeric part of the last ID
            $lastNumericPart = intval(substr($lastId, -5));
        } else {
            $lastNumericPart = 0;
        }

        do {
            // Increment the numeric part
            $lastNumericPart++;
            $newNumericPart = str_pad($lastNumericPart, 5, '0', STR_PAD_LEFT);
            $newId = $idPrefix . $newNumericPart;
            // Check if the generated ID already exists
            $existingId = DB::table($table)->where($column, $newId)->exists();
        } while ($existingId);

        return $newId;
    }
}
