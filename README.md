Period
============

[![Author](http://img.shields.io/badge/author-@nyamsprod-blue.svg?style=flat-square)](https://twitter.com/nyamsprod)
[![Latest Version](https://img.shields.io/github/release/thephpleague/period.svg?style=flat-square)](https://github.com/thephpleague/period/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build](https://github.com/thephpleague/period/workflows/build/badge.svg)](https://github.com/thephpleague/period/actions?query=workflow%3A%22build%22)
[![Infection MSI](https://badge.stryker-mutator.io/github.com/thephpleague/period/master)](https://infection.github.io)
[![Total Downloads](https://img.shields.io/packagist/dt/league/period.svg?style=flat-square)](https://packagist.org/packages/league/period)

`Period` is PHP's missing time range API. Based on ideas from [Resolving Feature Envy in the Domain](http://verraes.net/2014/08/resolving-feature-envy-in-the-domain/) by Mathias Verraes, this package extends the concept to cover all basic operations regarding time ranges.

## Highlights

- Represents Interval, Datepoint, Duration and Collection as value objects
- Exposes named constructors to ease object creation
- Covers all basic manipulations related to time range
- Enables working with simple or complex time ranges logic
- Fully documented
- Framework-agnostic

Documentation
-------

Full documentation can be found at [period.thephpleague.com](http://period.thephpleague.com).

System Requirements
-------

You need **PHP >= 7.2.0** but the latest stable version of PHP is recommended.

Install
-------

Install `Period` using Composer.

```
$ composer require league/period
```

or download the library and:

- use any other [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible autoloader.
- use the bundle autoloader script as shown below:

~~~php
require 'path/to/period/repo/autoload.php';

use League\Period\Datepoint;

Datepoint::create('2012-05-23')->getDay()->getDateInterval();
//returns new DateInterval('P1D');
~~~

where `path/to/period/repo` represents the path where the library was extracted.

Testing
-------

`Period` has:

- a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/).
- a code analysis compliance test suite using [PHPStan](https://github.com/phpstan/phpstan).
- a [PHPUnit](https://phpunit.de) test suite
- an optional [mutation test run](https://github.com/infection/infection)

To run the tests, run the following command from the project folder.


``` bash
$ composer test
```

Contributing
-------

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

Security
-------

If you discover any security related issues, please email nyamsprod@gmail.com instead of using the issue tracker.

Changelog
-------

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

Credits
-------

- [Ignace Nyamagana Butera](https://github.com/nyamsprod)
- [All Contributors](https://github.com/thephpleague/period/graphs/contributors)

License
-------

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

## Sponsor
![Scheduling API](https://user-images.githubusercontent.com/9488406/125080407-0dd25780-e0c5-11eb-9f70-ef958968674a.png)

This repo is sponsored by [**Spurwing**](https://www.spurwing.io/), where their API Makes Adding Scheduling Quick, Reliable and Scalable.
Use Spurwing to build and integrate Scheduling, Booking & Calendar features in your project. Read more about Spurwing [**Scheduling API on GitHub**](https://github.com/Spurwing/Appointment-Scheduling-API).
