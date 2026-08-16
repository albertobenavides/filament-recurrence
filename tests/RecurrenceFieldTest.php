<?php

namespace Andreia\FilamentRecurrence\Tests;

use Andreia\FilamentRecurrence\Forms\Components\RecurrenceField;
use InvalidArgumentException;

class RecurrenceFieldTest extends TestCase
{
    public function test_all_supported_fields_can_be_made_nullable(): void
    {
        $field = TestableRecurrenceField::make('recurrence')->nullable();

        $this->assertTrue($field->recurrenceFieldIsNullable('start_date'));
        $this->assertTrue($field->recurrenceFieldIsNullable('frequency'));
        $this->assertTrue($field->recurrenceFieldIsNullable('timezone'));
        $this->assertNull($field->defaultFor('start_date', now()));
        $this->assertNull($field->defaultFor('frequency', 'WEEKLY'));
        $this->assertNull($field->defaultFor('timezone', 'UTC'));
    }

    public function test_only_selected_fields_are_nullable(): void
    {
        $field = TestableRecurrenceField::make('recurrence')
            ->nullable(fields: ['timezone']);

        $this->assertFalse($field->recurrenceFieldIsNullable('start_date'));
        $this->assertFalse($field->recurrenceFieldIsNullable('frequency'));
        $this->assertTrue($field->recurrenceFieldIsNullable('timezone'));
        $this->assertSame('WEEKLY', $field->defaultFor('frequency', 'WEEKLY'));
        $this->assertNull($field->defaultFor('timezone', 'UTC'));
    }

    public function test_fields_can_be_passed_as_the_first_argument(): void
    {
        $field = TestableRecurrenceField::make('recurrence')
            ->nullable(['start_date', 'frequency']);

        $this->assertTrue($field->recurrenceFieldIsNullable('start_date'));
        $this->assertTrue($field->recurrenceFieldIsNullable('frequency'));
        $this->assertFalse($field->recurrenceFieldIsNullable('timezone'));
    }

    public function test_empty_nullable_recurrence_dehydrates_to_null(): void
    {
        $field = TestableRecurrenceField::make('recurrence')->nullable();

        $this->assertNull($field->dehydrateRecurrence([
            'frequency' => null,
            'interval' => 1,
            'start_date' => null,
            'timezone' => null,
        ]));
    }

    public function test_empty_nullable_timezone_remains_null_when_dehydrated(): void
    {
        $field = TestableRecurrenceField::make('recurrence')
            ->nullable(fields: 'timezone');

        $state = $field->dehydrateRecurrence([
            'frequency' => 'DAILY',
            'interval' => 1,
            'start_date' => '2026-08-15 12:00:00',
            'timezone' => null,
        ]);

        $this->assertIsArray($state);
        $this->assertNull($state['timezone']);
    }

    public function test_invalid_nullable_fields_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TestableRecurrenceField::make('recurrence')->nullable(['unknown']);
    }

    public function test_frequency_is_disabled_until_nullable_start_date_is_filled(): void
    {
        $field = TestableRecurrenceField::make('recurrence')
            ->nullable()
            ->showStartDate();

        $this->assertTrue($field->frequencyDisabledUntilStartDate(null));
        $this->assertTrue($field->frequencyDisabledUntilStartDate(''));
        $this->assertFalse($field->frequencyDisabledUntilStartDate('2026-08-15 12:00:00'));
    }

    public function test_frequency_stays_enabled_when_start_date_is_not_nullable(): void
    {
        $field = TestableRecurrenceField::make('recurrence')
            ->nullable(fields: ['timezone'])
            ->showStartDate();

        $this->assertFalse($field->frequencyDisabledUntilStartDate(null));
    }
}

class TestableRecurrenceField extends RecurrenceField
{
    public function recurrenceFieldIsNullable(string $field): bool
    {
        return $this->isRecurrenceFieldNullable($field);
    }

    public function defaultFor(string $field, mixed $default): mixed
    {
        return $this->recurrenceFieldDefault($field, $default);
    }

    public function dehydrateRecurrence(mixed $state): mixed
    {
        return $this->dehydrateRecurrenceState($state);
    }

    public function frequencyDisabledUntilStartDate(mixed $startDate): bool
    {
        return $this->isFrequencyDisabledUntilStartDate($startDate);
    }
}
