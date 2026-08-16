<?php

return [
    'fields' => [
        'recurrence' => [
            'label' => 'Recurrence',
            'start_date' => 'Start Date',
            'start_date_time' => 'Start Date & Time',
            'repeats' => 'Repeats',
            'fused_repeats' => 'Repeat every',
            'select_frequency' => 'Select a frequency',
            'timezone' => 'Timezone',
            'interval' => 'Interval',
            'repeat_on' => 'Repeat on',
            'repeat_by' => 'Repeat by',
            'day_of_month' => 'Day of month',
            'day_of_week' => 'Day of week',
            'in_months' => 'In months',
            'ends' => 'Ends',
            'never' => 'Never',
            'on_date' => 'On date',
            'after_occurrences' => 'After occurrences',
            'end_date' => 'End date',
            'occurrences' => 'Number of occurrences',
            'preview' => 'Preview',
            'next_occurrences' => 'Next occurrences',
            'preview_on_calendar' => 'Preview on calendar',
            'calendar_modal_close' => 'Close',
        ],
    ],

    'frequencies' => [
        'DAILY' => 'Daily',
        'WEEKLY' => 'Weekly',
        'MONTHLY' => 'Monthly',
        'YEARLY' => 'Yearly',
    ],

    /*
    | Used for the frequency select labels; pluralized from the numeric interval.
    */
    'frequency_units' => [
        'daily' => '{1} day|[2,*] days',
        'weekly' => '{1} week|[2,*] weeks',
        'monthly' => '{1} month|[2,*] months',
        'yearly' => '{1} year|[2,*] years',
    ],

    'intervals' => [
        'days' => 'day(s)',
        'weeks' => 'week(s)',
        'months' => 'month(s)',
        'years' => 'year(s)',
    ],

    'weekdays' => [
        'MO' => 'Monday',
        'TU' => 'Tuesday',
        'WE' => 'Wednesday',
        'TH' => 'Thursday',
        'FR' => 'Friday',
        'SA' => 'Saturday',
        'SU' => 'Sunday',
    ],

    /*
    | Single-letter labels for circular weekday toggles (Sunday–Saturday).
    */
    'weekday_letters' => [
        'SU' => 'S',
        'MO' => 'M',
        'TU' => 'T',
        'WE' => 'W',
        'TH' => 'T',
        'FR' => 'F',
        'SA' => 'S',
    ],

    'positions' => [
        '1' => 'First',
        '2' => 'Second',
        '3' => 'Third',
        '4' => 'Fourth',
        '-1' => 'Last',
    ],

    'months' => [
        '1' => 'January',
        '2' => 'February',
        '3' => 'March',
        '4' => 'April',
        '5' => 'May',
        '6' => 'June',
        '7' => 'July',
        '8' => 'August',
        '9' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December',
    ],

    'messages' => [
        'no_recurrence' => 'No recurrence',
        'invalid_recurrence' => 'Invalid recurrence pattern',
        'unable_to_preview' => 'Unable to generate preview',
    ],

    /*
    | Fragments appended to the Recurr pattern in previews (comma-separated).
    */
    'preview' => [
        'repeats' => 'Repeats :pattern',
        'starting_only' => 'starting :date',
        'until_only' => 'until :date',
        'date_range' => 'starting :start to :end',
        'for_occurrences' => 'for :count occurrences',
        'at_time' => 'at :time',
    ],
];
