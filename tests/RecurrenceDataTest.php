<?php

namespace Andreia\FilamentRecurrence\Tests;

use Carbon\Carbon;
use Andreia\FilamentRecurrence\Data\RecurrenceData;

test('can create recurrence data from array', function () {
    $data = RecurrenceData::fromArray([
        'frequency' => 'DAILY',
        'interval' => 1,
        'start_date' => '2024-01-01 09:00:00',
        'count' => 10,
    ]);

    expect($data->frequency)->toBe('DAILY')
        ->and($data->interval)->toBe(1)
        ->and($data->count)->toBe(10);
});

test('from array treats empty by_set_pos as null like cleared Filament Select', function () {
    $data = RecurrenceData::fromArray([
        'frequency' => 'MONTHLY',
        'interval' => 1,
        'start_date' => '2026-05-02 23:33:42',
        'by_day' => ['TU', 'WE'],
        'by_set_pos' => '',
    ]);

    expect($data->bySetPos)->toBeNull();
});

test('can convert recurrence data to rule', function () {
    $data = new RecurrenceData(
        frequency: 'WEEKLY',
        interval: 2,
        byDay: ['MO', 'WE', 'FR'],
        count: 10,
    );

    $rule = $data->toRule();

    expect($rule)->toContain('FREQ=WEEKLY')
        ->and($rule)->toContain('INTERVAL=2')
        ->and($rule)->toContain('BYDAY=MO,WE,FR')
        ->and($rule)->toContain('COUNT=10');
});

test('can parse rule string to recurrence data', function () {
    $rule = 'FREQ=DAILY;INTERVAL=3;COUNT=5';
    $data = RecurrenceData::fromRule($rule);

    expect($data->frequency)->toBe('DAILY')
        ->and($data->interval)->toBe(3)
        ->and($data->count)->toBe(5);
});

test('can generate human readable description', function () {
    $data = new RecurrenceData(
        frequency: 'WEEKLY',
        interval: 1,
        startDate: Carbon::now(),
        byDay: ['MO', 'FR'],
    );

    $description = $data->toHumanReadable();

    expect($description)->toBeString()
        ->and($description)->toContain('Repeats')
        ->and($description)->toContain('week');
});

test('human readable preview time uses start datetime without timezone conversion', function () {
    $data = new RecurrenceData(
        frequency: 'DAILY',
        interval: 1,
        startDate: Carbon::parse('2026-05-06 17:49:00', 'UTC'),
        timezone: 'America/New_York',
    );

    expect($data->toHumanReadable())->toContain('17:49');
});

test('human readable preview includes start end dates time and occurrence count when set', function () {
    $withRangeAndTime = new RecurrenceData(
        frequency: 'MONTHLY',
        interval: 1,
        startDate: Carbon::parse('2026-08-26 10:30:00', 'UTC'),
        endDate: Carbon::parse('2026-12-26 23:59:59', 'UTC'),
        byMonthDay: [16],
        timezone: 'UTC',
    );

    expect($withRangeAndTime->toHumanReadable())
        ->toContain('Repeats')
        ->and($withRangeAndTime->toHumanReadable())->toContain('starting')
        ->and($withRangeAndTime->toHumanReadable())->toContain('26 August 2026')
        ->and($withRangeAndTime->toHumanReadable())->toContain('26 December 2026')
        ->and($withRangeAndTime->toHumanReadable())->toContain('10:30');

    $withCount = new RecurrenceData(
        frequency: 'DAILY',
        interval: 1,
        startDate: Carbon::parse('2026-01-01', 'UTC'),
        count: 5,
        timezone: 'UTC',
    );

    expect($withCount->toHumanReadable())->toContain('for 5 occurrences');
});

test('can get occurrences', function () {
    $startDate = Carbon::parse('2024-01-01 09:00:00');

    $data = new RecurrenceData(
        frequency: 'DAILY',
        interval: 1,
        startDate: $startDate,
        count: 5,
    );

    $occurrences = $data->getOccurrences(5);

    expect($occurrences)->toHaveCount(5)
        ->and($occurrences[0])->toBeInstanceOf(Carbon::class)
        ->and($occurrences[0]->format('Y-m-d'))->toBe('2024-01-01');
});

test('weekly recurrence on specific days', function () {
    $startDate = Carbon::parse('2024-01-01'); // Monday

    $data = new RecurrenceData(
        frequency: 'WEEKLY',
        interval: 1,
        startDate: $startDate,
        byDay: ['MO', 'WE', 'FR'],
        count: 9,
    );

    $occurrences = $data->getOccurrences(9);

    expect($occurrences)->toHaveCount(9);

    // First three should be Mon, Wed, Fri
    expect($occurrences[0]->dayOfWeek)->toBe(Carbon::MONDAY)
        ->and($occurrences[1]->dayOfWeek)->toBe(Carbon::WEDNESDAY)
        ->and($occurrences[2]->dayOfWeek)->toBe(Carbon::FRIDAY);
});

test('monthly week chosen without weekdays never emits orphan BYSETPOS', function () {
    $data = new RecurrenceData(
        frequency: 'MONTHLY',
        interval: 1,
        startDate: Carbon::parse('2026-05-06 17:49:00'),
        byDay: null,
        bySetPos: 2,
        timezone: 'UTC',
    );

    expect($data->toRule())->toBe('FREQ=MONTHLY')
        ->and($data->toRule())->not->toContain('BYSETPOS')
        ->and($data->getOccurrences(5))->toHaveCount(0)
        ->and($data->toHumanReadable())->toBe(__('filament-recurrence::recurrence.messages.unable_to_preview'));
});

test('monthly nth weekdays use ordinal BYDAY tokens without BYSETPOS', function () {
    $data = new RecurrenceData(
        frequency: 'MONTHLY',
        interval: 1,
        startDate: Carbon::parse('2026-05-06 17:49:00'),
        byDay: ['MO', 'TU', 'WE', 'FR'],
        bySetPos: 4,
        timezone: 'UTC',
    );

    expect($data->toRule())->toBe('FREQ=MONTHLY;BYDAY=4MO,4TU,4WE,4FR')
        ->and($data->toRule())->not->toContain('BYSETPOS');
});

test('monthly fourth weekdays expands each selected weekday in the month', function () {
    $data = new RecurrenceData(
        frequency: 'MONTHLY',
        interval: 1,
        startDate: Carbon::parse('2026-05-06 17:49:00'),
        byDay: ['MO', 'TU', 'WE', 'FR'],
        bySetPos: 4,
        timezone: 'UTC',
    );

    $occ = $data->getOccurrences(8);

    expect($occ[0]->format('Y-m-d'))->toBe('2026-05-22')
        ->and($occ[1]->format('Y-m-d'))->toBe('2026-05-25')
        ->and($occ[2]->format('Y-m-d'))->toBe('2026-05-26')
        ->and($occ[3]->format('Y-m-d'))->toBe('2026-05-27');
});

test('from rule parses ordinal monthly BYDAY into plain weekdays and week position', function () {
    $data = RecurrenceData::fromRule('FREQ=MONTHLY;BYDAY=-1FR,-1MO');

    expect($data->frequency)->toBe('MONTHLY')
        ->and($data->byDay)->toBe(['FR', 'MO'])
        ->and($data->bySetPos)->toBe(-1);
});

test('from rule keeps legacy monthly plain BYDAY with BYSETPOS', function () {
    $data = RecurrenceData::fromRule('FREQ=MONTHLY;BYDAY=MO,TU,WE,FR;BYSETPOS=4');

    expect($data->frequency)->toBe('MONTHLY')
        ->and($data->byDay)->toBe(['MO', 'TU', 'WE', 'FR'])
        ->and($data->bySetPos)->toBe(4);
});

test('monthly recurrence on specific day of month', function () {
    $startDate = Carbon::parse('2024-01-15');

    $data = new RecurrenceData(
        frequency: 'MONTHLY',
        interval: 1,
        startDate: $startDate,
        byMonthDay: [15],
        count: 3,
    );

    $occurrences = $data->getOccurrences(3);

    expect($occurrences)->toHaveCount(3)
        ->and($occurrences[0]->day)->toBe(15)
        ->and($occurrences[1]->day)->toBe(15)
        ->and($occurrences[2]->day)->toBe(15);
});

test('yearly recurrence', function () {
    $startDate = Carbon::parse('2024-06-15');

    $data = new RecurrenceData(
        frequency: 'YEARLY',
        interval: 1,
        startDate: $startDate,
        byMonth: [6],
        byMonthDay: [15],
        count: 3,
    );

    $occurrences = $data->getOccurrences(3);

    expect($occurrences)->toHaveCount(3)
        ->and($occurrences[0]->format('m-d'))->toBe('06-15')
        ->and($occurrences[1]->format('m-d'))->toBe('06-15')
        ->and($occurrences[2]->format('m-d'))->toBe('06-15');
});

test('recurrence with end date', function () {
    $startDate = Carbon::parse('2024-01-01');
    $endDate = Carbon::parse('2024-01-10');

    $data = new RecurrenceData(
        frequency: 'DAILY',
        interval: 1,
        startDate: $startDate,
        endDate: $endDate,
    );

    $occurrences = $data->getOccurrences(100);

    expect(count($occurrences))->toBeLessThanOrEqual(10);
});

test('converts to array correctly', function () {
    $data = new RecurrenceData(
        frequency: 'WEEKLY',
        interval: 2,
        startDate: Carbon::parse('2024-01-01'),
        byDay: ['MO', 'WE'],
    );

    $array = $data->toArray();

    expect($array)->toHaveKey('frequency')
        ->and($array)->toHaveKey('interval')
        ->and($array)->toHaveKey('start_date')
        ->and($array)->toHaveKey('by_day')
        ->and($array)->toHaveKey('rule')
        ->and($array)->toHaveKey('end_type')
        ->and($array['end_type'])->toBe('never')
        ->and($array['frequency'])->toBe('WEEKLY')
        ->and($array['interval'])->toBe(2);
});

test('mergeFormUiState infers end_type from count or end_date', function () {
    expect(RecurrenceData::mergeFormUiState([
        'frequency' => 'WEEKLY',
        'count' => 2,
        'by_day' => ['TU', 'TH'],
    ])['end_type'])->toBe('count');

    expect(RecurrenceData::mergeFormUiState([
        'frequency' => 'DAILY',
        'end_date' => '2026-12-31 00:00:00',
    ])['end_type'])->toBe('date');

    expect(RecurrenceData::mergeFormUiState([
        'frequency' => 'DAILY',
        'count' => null,
        'end_date' => null,
    ])['end_type'])->toBe('never');
});

test('mergeFormUiState infers monthly_type for monthly recurrence', function () {
    expect(RecurrenceData::mergeFormUiState([
        'frequency' => 'MONTHLY',
        'by_month_day' => [15],
    ])['monthly_type'])->toBe('day');

    expect(RecurrenceData::mergeFormUiState([
        'frequency' => 'MONTHLY',
        'by_set_pos' => '2',
        'by_day' => ['TU'],
    ])['monthly_type'])->toBe('weekday');
});

test('mergeFormUiState defaults timezone from config when missing', function () {
    config(['filament-recurrence.timezone' => 'Europe/Lisbon']);

    expect(RecurrenceData::mergeFormUiState([
        'frequency' => 'DAILY',
    ])['timezone'])->toBe('Europe/Lisbon');
});

test('from array preserves an explicitly empty timezone as null', function () {
    config(['filament-recurrence.timezone' => 'America/Sao_Paulo']);

    $data = RecurrenceData::fromArray([
        'frequency' => 'DAILY',
        'interval' => 1,
        'start_date' => '2024-01-01',
        'timezone' => '',
    ]);

    expect($data->timezone)->toBeNull();
});

test('mergeFormUiState preserves explicit end_type', function () {
    $state = RecurrenceData::mergeFormUiState([
        'frequency' => 'WEEKLY',
        'count' => 2,
        'end_type' => 'never',
    ]);

    expect($state['end_type'])->toBe('never');
});

test('handles invalid rule gracefully', function () {
    $data = RecurrenceData::fromRule('INVALID_RULE');

    expect($data->frequency)->toBeNull();
});

test('handles empty data', function () {
    $data = new RecurrenceData();

    expect($data->toRule())->toBe('')
        ->and($data->toHumanReadable())->toBe('No recurrence')
        ->and($data->getOccurrences())->toBeArray()
        ->and($data->getOccurrences())->toHaveCount(0);
});
