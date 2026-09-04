<?php

if (!function_exists('resolve_project_date_range')) {
    /**
     * Resolve start and end filter date into a validated range array.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array{start_date: string, end_date: string}|null
     */
    function resolve_project_date_range(string $startDate, string $endDate): ?array
    {
        $startDate = is_valid_filter_date($startDate) ? $startDate : '';
        $endDate = is_valid_filter_date($endDate) ? $endDate : '';

        if ($startDate === '' && $endDate === '') {
            return null;
        }

        if ($startDate === '') {
            $startDate = $endDate;
            $endDate = get_next_filter_date($startDate);
        } elseif ($endDate === '') {
            $endDate = get_next_filter_date($startDate);
        }

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}

if (!function_exists('is_valid_filter_date')) {
    /**
     * Check if date string matches Y-m-d format.
     *
     * @param string $date
     * @return bool
     */
    function is_valid_filter_date(string $date): bool
    {
        $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsedDate !== false && $parsedDate->format('Y-m-d') === $date;
    }
}

if (!function_exists('get_next_filter_date')) {
    /**
     * Get the next calendar day string in Y-m-d format.
     *
     * @param string $date
     * @return string
     */
    function get_next_filter_date(string $date): string
    {
        return (new \DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d');
    }
}
