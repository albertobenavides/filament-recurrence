<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    @php
        $humanReadable = $getHumanReadable();
        $rule = $getRule();
        $details = $getDetails();
        $nextOccurrences = $getNextOccurrences();
    @endphp

    @if ($humanReadable)
        <div class="space-y-4">
            <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                <div class="text-base font-semibold text-gray-950 dark:text-white">
                    {{ $humanReadable }}
                </div>

                @if ($rule)
                    <div class="mt-2 text-xs font-mono text-gray-500 dark:text-gray-400 break-all">
                        <strong>RRULE:</strong> {{ $rule }}
                    </div>
                @endif
            </div>

            @if (count($details) > 0)
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($details as $label => $value)
                        <div>
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ $label }}
                            </div>
                            <div class="text-sm text-gray-950 dark:text-white">
                                {{ $value }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (count($nextOccurrences) > 0)
                <div>
                    <div class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Upcoming Occurrences
                    </div>
                    <div class="space-y-2">
                        @foreach ($nextOccurrences as $index => $occurrence)
                            <div class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-500 text-xs font-bold text-white">
                                    {{ $index + 1 }}
                                </div>
                                <div class="text-sm text-gray-950 dark:text-white">
                                    {{ $occurrence->format(config('filament-recurrence.date_format') . ' ' . config('filament-recurrence.time_format')) }}
                                </div>
                                <div class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                                    {{ $occurrence->diffForHumans() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400">
            No recurrence pattern defined
        </div>
    @endif
</x-dynamic-component>
