<?php

if (!function_exists('get_quarter_date_range')) {
    /**
     * Convert quarter and year into a date range.
     *
     * @return array{start_date:string,end_date:string}|null
     */
    function get_quarter_date_range($quarter, $year): ?array
    {
        $quarter = (string) $quarter;
        $year = (int) $year;

        if ($year <= 0) {
            return null;
        }

        $quarterMap = [
            '1' => ['01-01', '03-31'],
            '2' => ['04-01', '06-30'],
            '3' => ['07-01', '09-30'],
            '4' => ['10-01', '12-31'],
        ];

        if (!isset($quarterMap[$quarter])) {
            return null;
        }

        [$startSuffix, $endSuffix] = $quarterMap[$quarter];

        return [
            'start_date' => $year . '-' . $startSuffix,
            'end_date' => $year . '-' . $endSuffix,
        ];
    }
}
