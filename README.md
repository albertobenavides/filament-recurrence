# Filament Recurrence Plugin

A full-featured Filament PHP plugin for handling recurrence patterns with form fields, table columns, and infolist components. 

## Features

- **Complete Recurrence Support** - Daily, Weekly, Monthly, and Yearly patterns
- **Form Field Component** - Beautiful, reactive form fields for defining recurrence rules
- **Table Column Component** - Display recurrence patterns in your Filament tables
- **Infolist Entry Component** - Detailed recurrence information in your infolists
- **Type-Safe** - Full PHP 8.3+ type hints with RecurrenceData DTO
- **Eloquent Cast** - Easy model integration with custom cast
- **Model Trait** - Helper methods for working with recurring events
- **RRULE Compatible** - Full RFC 5545 iCalendar recurrence rule support
- **Customizable** - Extensive configuration options
- **Per-record timezone** - Timezone select stored with recurrence JSON; defaults from `config/filament-recurrence.php`

[![Filament Recurrence Demo Video](https://raw.githubusercontent.com/andreia/filament-recurrence/main/art/demo_video.jpg)](https://www.youtube.com/watch?v=NOg2IYgJ1W4)

## Requirements

- PHP 8.3+
- Laravel 12+
- Filament 4/5

## Dependencies

Built on top of the powerful [simshaun/recurr](https://github.com/simshaun/recurr) package.

The recurrence form uses [`tapp/filament-timezone-field`](https://github.com/TappNetwork/filament-timezone-field) for the timezone dropdown.

Composer installs both automatically as a dependency of this package.

## Appearance

### Form Field

![Form Field Example 1](https://raw.githubusercontent.com/andreia/filament-recurrence/main/art/form-field.png)

![Form Field Example 2](https://raw.githubusercontent.com/andreia/filament-recurrence/main/art/form-field1.png)

![Form Field Example 3](https://raw.githubusercontent.com/andreia/filament-recurrence/main/art/form-field2.png)

![Form Field Example 4](https://raw.githubusercontent.com/andreia/filament-recurrence/main/art/form-field3.png)

### Table Column

![Table Column](https://raw.githubusercontent.com/andreia/filament-recurrence/main/art/table-column.png)

### Infolist

![Infolist Entry](https://raw.githubusercontent.com/andreia/filament-recurrence/main/art/infolist.png)

## Installation

Install the package via Composer:

```bash
composer require andreia/filament-recurrence
```

### Database

Recurrence is stored as JSON on **your** model’s table. Add a nullable `json` column (name it however you like; the docs assume `recurrence`):

```bash
php artisan make:migration add_recurrence_to_your_table
```

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('your_table', function (Blueprint $table) {
            $table->json('recurrence')->nullable()->after('your_column');
        });
    }

    public function down(): void
    {
        Schema::table('your_table', function (Blueprint $table) {
            $table->dropColumn('recurrence');
        });
    }
};
```

```bash
php artisan migrate
```

### Configuration (optional)

Publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-recurrence-config"
```

Available options in `config/filament-recurrence.php`:

The `timezone` value is the **default** for new recurrence records and data without a stored timezone. Each record can override it using the **Timezone** field; the chosen identifier (e.g. `Europe/Berlin`) is persisted in the recurrence payload alongside other fields.

```php
return [
    'timezone' => 'UTC',
    'date_format' => 'Y-m-d',
    'time_format' => 'H:i',
    'max_preview_occurrences' => 10,
    'frequencies' => [
        'DAILY' => 'Daily',
        'WEEKLY' => 'Weekly',
        'MONTHLY' => 'Monthly',
        'YEARLY' => 'Yearly',
    ],
    'week_start_day' => 1, // Monday
    'enable_advanced_options' => true,
];
```

Add to your Filament `theme.css`:

```css
@source '../../../../vendor/andreia/filament-recurrence';
```

and run `npm run build` or `bun run build`.

## Model Setup

### Using the Cast

Add the cast to your model:

```php
use Andreia\FilamentRecurrence\Casts\RecurrenceCast;

class Event extends Model
{
    protected $casts = [
        'recurrence' => RecurrenceCast::class,
    ];
}
```

### Using the Trait

Add helpful methods to your model:

```php
use Andreia\FilamentRecurrence\Concerns\HasRecurrence;

class Event extends Model
{
    use HasRecurrence;

    protected $casts = [
        'recurrence' => RecurrenceCast::class,
    ];
}

// Now you can use:
$event->getRecurrenceData(); // Get RecurrenceData object
$event->getNextOccurrence(); // Get next occurrence as Carbon
$event->getUpcomingOccurrences(10); // Get next 10 occurrences
$event->occursOn(Carbon::parse('2024-12-25')); // Check if occurs on date

// Query scopes:
Event::occursOn(Carbon::today())->get();
Event::occursBetween(Carbon::now(), Carbon::now()->addMonth())->get();
```

## Basic Usage

### Form Field

Add the recurrence field to your Filament form:

```php
use Andreia\FilamentRecurrence\Forms\Components\RecurrenceField;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('title')->required(),
            
            RecurrenceField::make('recurrence')
                ->showStartDate()
                ->showEndOptions()
                ->useDateTime(), // Use DateTimePicker instead of DatePicker
        ]);
}
```

### Table Column

Display recurrence patterns in your table:

```php
use Andreia\FilamentRecurrence\Tables\Columns\RecurrenceColumn;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('title'),
            
            RecurrenceColumn::make('recurrence')
                ->showNextOccurrences(limit: 3)
                ->showRule(),
        ]);
}
```

### Infolist Entry

Show detailed recurrence information:

```php
use Andreia\FilamentRecurrence\Infolists\Components\RecurrenceEntry;

public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            TextEntry::make('title'),
            
            RecurrenceEntry::make('recurrence')
                ->showAllDetails()
                ->showNextOccurrences(limit: 10)
                ->showRule(),
        ]);
}
```

## Advanced Usage

### Working with RecurrenceData

The `RecurrenceData` DTO provides a clean interface for working with recurrence patterns:

```php
use Andreia\FilamentRecurrence\Data\RecurrenceData;
use Carbon\Carbon;

// Create from array
$data = RecurrenceData::fromArray([
    'frequency' => 'WEEKLY',
    'interval' => 2,
    'start_date' => Carbon::now(),
    'by_day' => ['MO', 'WE', 'FR'],
    'count' => 10,
]);

// Create from RRULE string
$data = RecurrenceData::fromRule('FREQ=DAILY;INTERVAL=1;COUNT=5');

// Convert to RRULE string
$rule = $data->toRule();

// Get human-readable description
$description = $data->toHumanReadable(); // "Every 2 weeks on Monday, Wednesday, Friday, 10 times"

// Get occurrences
$occurrences = $data->getOccurrences(limit: 5);

// Convert to array
$array = $data->toArray();
```

### Form Field Customization

```php
RecurrenceField::make('recurrence')
    ->showStartDate(true) // Show/hide start date picker
    ->showEndOptions(true) // Show/hide end options (never, until, count)
    ->showPreview(true) // Show/hide the live preview (human-readable rule + next occurrences); default is true
    ->previewOccurrencesLimit(5) // How many “next occurrences” rows to show in the preview; default is 5
    ->useDateTime(true) // Use datetime picker instead of date picker
    ->showTimezone(true) // Show/hide the per-record timezone select (default true); when false, config timezone is always used
    ->nullable() // Allow start date, frequency, and timezone to be empty; empty recurrence is stored as null. When start date is empty, “Repeat every” stays disabled until a start date is set.
    ->showAdvancedOptions(true) // Show advanced recurrence options

// Make only selected recurrence fields nullable:
RecurrenceField::make('recurrence')->nullable(fields: ['timezone']);

// Hide the preview (full-width form only):
RecurrenceField::make('recurrence')->showPreview(false);

// Use only `config('filament-recurrence.timezone')` for every record (hide the timezone field):
RecurrenceField::make('recurrence')->showTimezone(false);

// Show 10 upcoming dates in the preview:
RecurrenceField::make('recurrence')->previewOccurrencesLimit(10);
```

### Table Column Customization

```php
RecurrenceColumn::make('recurrence')
    ->showRule(true) // Display the RRULE string
    ->showNextOccurrences(true, limit: 5) // Show next N occurrences
```

### Infolist Entry Customization

```php
RecurrenceEntry::make('recurrence')
    ->showRule(true) // Display the RRULE string
    ->showAllDetails(true) // Show all recurrence details
    ->showNextOccurrences(true, limit: 20) // Show next N occurrences
```

## Recurrence Patterns Examples

### Daily Patterns

```php
// Every day
[
    'frequency' => 'DAILY',
    'interval' => 1,
    'start_date' => Carbon::now(),
]

// Every 3 days, 10 times
[
    'frequency' => 'DAILY',
    'interval' => 3,
    'count' => 10,
]
```

### Weekly Patterns

```php
// Every week on Monday and Friday
[
    'frequency' => 'WEEKLY',
    'interval' => 1,
    'by_day' => ['MO', 'FR'],
]

// Every 2 weeks on weekdays
[
    'frequency' => 'WEEKLY',
    'interval' => 2,
    'by_day' => ['MO', 'TU', 'WE', 'TH', 'FR'],
]
```

### Monthly Patterns

```php
// Every month on the 15th
[
    'frequency' => 'MONTHLY',
    'interval' => 1,
    'by_month_day' => [15],
]

// Every month on the first Monday
[
    'frequency' => 'MONTHLY',
    'interval' => 1,
    'by_day' => ['MO'],
    'by_set_pos' => 1,
]

// Every month on the last Friday
[
    'frequency' => 'MONTHLY',
    'interval' => 1,
    'by_day' => ['FR'],
    'by_set_pos' => -1,
]
```

### Yearly Patterns

```php
// Every year on January 1st
[
    'frequency' => 'YEARLY',
    'interval' => 1,
    'by_month' => [1],
    'by_month_day' => [1],
]

// Every year in June and December
[
    'frequency' => 'YEARLY',
    'interval' => 1,
    'by_month' => [6, 12],
]
```

## Testing

```bash
composer test
```

## Code Formatting

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- Built on [simshaun/recurr](https://github.com/simshaun/recurr)
- Timezone select from [TappNetwork/filament-timezone-field](https://github.com/TappNetwork/filament-timezone-field)
- [Filament](https://filamentphp.com)

## Love this project? Help keep it growing! 🚀

I built Filament Recurrence to be a powerful tool for the community, and your support is what keeps it running. If this project has saved you time or solved a headache, consider showing your appreciation:

- **Spread the Word**: Share it, contribute code or feedback.

- **One-off Donation**: Support via [Stripe](https://donate.stripe.com/3cIeVf1x3eWS2xq8f3dby02). 

- **Become a Sponsor**: Help me reach my next development milestone by [Sponsoring on GitHub](https://github.com/sponsors/andreia).

- **Buying me a Coffee** – If you prefer [Buy me a Coffee](https://buymeacoffee.com/andreiabohner) platform

Thank you for supporting open-source development! ❤️

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

Made with ❤️ for the Filament community
