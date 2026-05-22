<?php

namespace Andreia\FilamentRecurrence\Infolists\Components;

use Filament\Infolists\Components\Entry;
use Andreia\FilamentRecurrence\Data\RecurrenceData;

class RecurrenceEntry extends Entry
{
    protected string $view = 'filament-recurrence::infolists.components.recurrence-entry';

    protected bool $showRule = false;
    protected bool $showAllDetails = false;
    protected bool $showNextOccurrences = true;
    protected int $nextOccurrencesLimit = 10;

    public function showRule(bool $condition = true): static
    {
        $this->showRule = $condition;
        return $this;
    }

    public function showAllDetails(bool $condition = true): static
    {
        $this->showAllDetails = $condition;
        return $this;
    }

    public function showNextOccurrences(bool $condition = true, int $limit = 10): static
    {
        $this->showNextOccurrences = $condition;
        $this->nextOccurrencesLimit = $limit;
        return $this;
    }

    public function getRecurrenceData(): ?RecurrenceData
    {
        $state = $this->getState();

        if ($state === null || $state === '') {
            return null;
        }

        if ($state instanceof RecurrenceData) {
            return $state;
        }

        try {
            if (is_string($state)) {
                return RecurrenceData::fromRule($state);
            }

            if (is_array($state)) {
                return RecurrenceData::fromArray($state);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getHumanReadable(): ?string
    {
        $data = $this->getRecurrenceData();
        return $data?->toHumanReadable();
    }

    public function getRule(): ?string
    {
        if (! $this->showRule) {
            return null;
        }

        $data = $this->getRecurrenceData();
        return $data?->toRule();
    }

    public function getDetails(): array
    {
        if (! $this->showAllDetails) {
            return [];
        }

        $data = $this->getRecurrenceData();

        if (! $data) {
            return [];
        }

        $details = [];

        if ($data->interval && $data->interval > 1) {
            $details['Interval'] = $data->formatIntervalLabel();
        }

        if ($data->frequency) {
            $details['Frequency'] = $data->formatFrequencyLabel();
        }

        if ($data->startDate) {
            $details['Start Date'] = $data->startDate->format(config('filament-recurrence.date_format'));
        }

        if ($data->endDate) {
            $details['End Date'] = $data->endDate->format(config('filament-recurrence.date_format'));
        }

        if ($data->count) {
            $details['Occurrences'] = $data->count;
        }

        if ($data->byDay && ! empty($data->byDay)) {
            $details['Days'] = implode(', ', $data->byDay);
        }

        if ($data->byMonthDay && ! empty($data->byMonthDay)) {
            $details['Month Days'] = implode(', ', $data->byMonthDay);
        }

        if ($data->byMonth && ! empty($data->byMonth)) {
            $details['Months'] = implode(', ', $data->byMonth);
        }

        return $details;
    }

    public function getNextOccurrences(): array
    {
        if (! $this->showNextOccurrences) {
            return [];
        }

        $data = $this->getRecurrenceData();
        return $data?->getOccurrences($this->nextOccurrencesLimit) ?? [];
    }
}
