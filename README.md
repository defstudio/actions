# Helpers methods for Laravel Actions

[![Latest Version on Packagist](https://img.shields.io/packagist/v/defstudio/actions.svg?style=flat-square)](https://packagist.org/packages/defstudio/actions)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/defstudio/actions/run-tests?label=tests)](https://github.com/defstudio/actions/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/defstudio/actions/Check%20&%20fix%20styling?label=code%20style)](https://github.com/defstudio/actions/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/defstudio/actions.svg?style=flat-square)](https://packagist.org/packages/defstudio/actions)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require defstudio/actions
```

## Usage

Add `use DefStudio\Actions\ActAsAction;` trait to your action class or extend `DefStudio\Actions\Action` 

(optional) add a dockblock to hint the static `run` method parameters and return types

```php
/**
 * @method static void run(Report|int $report)
 */
class DeleteReport
{
    use ActsAsAction;

    public function handle(Report|int $report): void
    {
        if (is_int($report)) {
            $report = Report::findOrFail($report);
        }

        DB::transaction(function () use ($report) {
            $report->delete_data();
            $report->delete();
        });
    }
}

class DeleteReport extends \DefStudio\Actions\Action
{
    public function handle(Report|int $report): void
    {
        if (is_int($report)) {
            $report = Report::findOrFail($report);
        }

        DB::transaction(function () use ($report) {
            $report->delete_data();
            $report->delete();
        });
    }
}
```

Use the new methods:

```php
DeleteReport::run($report->id);

DeleteReport::make()->handle($report->id);
```

Also, you can define a mock for the action (it will be authomatically bound to the app container):

```php
$mock = DeleteReport::mock(fn($report_id) => null);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Fabio Ivona](https://github.com/fabio-ivona)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
