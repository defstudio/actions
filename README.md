# Helpers methods for Laravel Actions

[![Latest Version on Packagist](https://img.shields.io/packagist/v/defstudio/actions.svg?style=flat-square)](https://packagist.org/packages/defstudio/actions)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/def-studio/actions/run-tests?label=tests)](https://github.com/def-studio/actions/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/def-studio/actions/Check%20&%20fix%20styling?label=code%20style)](https://github.com/def-studio/actions/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![GitHub Static Analysis Action Status](https://img.shields.io/github/workflow/status/def-studio/actions/PHPStan?label=phpstan)](https://github.com/def-studio/actions/actions?query=workflow%3Aphpstan+branch%3Amain)
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
    public function handle(Report|int $report): bool
    {
        if (is_int($report)) {
            $report = Report::findOrFail($report);
        }

        return DB::transaction(function () use ($report) {
            $report->delete_data();
            return $report->delete();
        });
    }
}
```

Use the new methods:

```php
$result = DeleteReport::run($report->id); //true

$result = DeleteReport::make()->handle($report->id); //true
```

## Run multiple actions as once

an action can be run multiple times by calling its `runMany()` method

```php
$results = DeleteReport::runMany($report1->id, $report2->id, $report3->id); //[true, false, true]
```

each run's result will be collected in an array and returned by `runMany()`

_notes_

if multiple parameters are required, they can be wrapped in an array (associative array keys will be treated as named arguments):

```php
class{
    use InjectsItself;
 
    public function handle($name = 'guest', $title = 'Mr.'): string
    {
        return "$title $name";
    }
}

$result = MyAwesomeAction::runMany(['Elizabeth', "Ms."], ['Fabio'],  ['title' => 'Mrs.']);

// $result = ["Ms. Elizabeth", "Mr. Fabio", "Mrs. guest"] 
```

## Mockable actions

Also, you can define a mock for the action (it will be authomatically bound to the app container):

```php
FindTheAnswerToLifeTheUniverseAndEverything::mock(fn ($report_id) => 42);

FindTheAnswerToLifeTheUniverseAndEverything::run() // 42
```

if you are interested in only mocking the return value, you can write:

```php
FindTheAnswerToLifeTheUniverseAndEverything::mock(42);
```

if your action has public methods other than `handle`, they can be mocked as well:

```php
MyWeirdAction::mock(handle: fn() => 5, handleForAdmin: fn() => 42);
```

without arguments, `mocks` returns a MockInterface instance ready to be used

```php
MyAction::mock()->shouldNotReceive('handle');
```

a partial mock (i.e. for actions with more than a single method)

```php
BuildOrder::partial_mock(fromRequest: fn() => true);

//this will not be mocked
BuildOrder::make()->fromJson($data);
```

along with mocks, actions can also be _spied_:

```php
$spiedAction = MyAction::spy();

$spiedAction->handle();
$spiedAction->handle();

$spiedAction->shouldHaveReceived()->handle()->twice()
```

## Dispatchable actions

An action can be made _dispatchable_ as a job with the `ActsAsJob` trait (or extending the `Action` class)

a job can be created by calling the `job()` static method:

```php
dispatch(LongRunningAction::job($argument_1, $argument_2));
```

or can be dispatched with its dedicated methods:

```php
LongRunningAction::dispatch($argument_1, $argument_2);

LongRunningAction::dispatchAfterResponse($argument_1, $argument_2);
```

The action will be dispatched wrapped in a ActionJob decorator that will proxy properties as needed:

```php
use DefStudio\Actions\Concerns\ActsAsJob;

class LongRunningAction{
    use ActsAsJob;
    
    public int $timeout = 2 * 60 * 60;
    public int $tries = 4;
    public array $backoff = [60, 120, 300, 600];
    public string $queue = 'long-running';
    
    public function handle(){...}
}
```

### Cleaning up after failed action job

Failed action jobs can be handled by defining a `jobFailed()` method:

```php
class LongRunningAction{
    use ActsAsJob;
       
    public function handle(){..}
    
    public function jobFailed($exception)
    {
        $this->handleFailure();
    }
    
    private function handleFailure(){..}
}
```

### Batches and Chains of action jobs

Similarly to the `runMany()` method, a new batch/chain of action jobs can be created starting from an array of parameters:

```php
MyAction::batch([$name1, $title1], [$name2, $title2])->dispatch();

MyAction::chain([$name1, $title1], [$name2, $title2])->dispatch();
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

This project was inspired and is built as an opinionated and simplified implementation of [Loris Leiva](https://github.com/lorisleiva)'s [Laravel Actions](https://laravelactions.com/). For a more powerful tool, you should take a look at it.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
