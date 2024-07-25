<?php

class Booking_Guestify_Calendar
{
    private $start;
    private $end;

    public function getAllDaysBetween($startDate, $endDate)
    {
        // Create DateTime objects for the start and end dates
        $this->start = new DateTime($startDate);
        $this->end = new DateTime($endDate);

        $start = $this->start;
        $end = $this->end;
        // Add one day to the end date to include it in the range
        $end->modify('+1 day');

        // Create a DatePeriod object for iterating over each day
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($start, $interval, $end);

        // Array to hold the result
        $daysArray = [];

        // Iterate over each day in the DatePeriod
        foreach ($dateRange as $date) {
            $daysArray[] = [
                'year' => $date->format('Y'),
                'month' => $date->format('M'),
                'day' => $date->format('d'),
                'week' => strtoupper($date->format('D'))
            ];
        }
        return $daysArray;
    }

    function countMonthsBetween() {
        $start = $this->start;
        $end = $this->end;

        // Calculate the difference between the two dates
        $diff = $start->diff($end);
    
        // Calculate the total number of months
        $months = ($diff->y * 12) + $diff->m;
    
        // If the day of the end date is greater than or equal to the start date, add 1 to the month count
        if ($end->format('d') >= $start->format('d')) {
            $months++;
        }
    
        return $months;
    }
}
