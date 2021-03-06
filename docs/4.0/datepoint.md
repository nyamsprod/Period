---
layout: default
title: The Datepoint class
---

# The Datepoint class

<p class="message-info">The <code>Datepoint</code> class is introduced in <code>version 4.2</code>.</p>

A datepoint is a position in time expressed as a `DateTimeImmutable` object.

The `Datepoint` class is introduced to ease `Datepoint` manipulation. This class extends PHP's `DateTimeImmutable` class by adding a new named constructor and several getter methods.

## Named constructor

### Datepoint::create

~~~php
public Datepoint::create($datepoint): self
~~~

Returns a `Datepoint` object or throws:

- a `TypeError` if the submitted parameter have the wrong type.

#### parameters

- `$datepoint` can be:
    - a `DateTimeInterface` implementing object
    - a string parsable by the `DateTime` constructor.
    - an integer interpreted as a timestamp.

<p class="message-info">Because we are using PHP's parser, values exceeding ranges will be added to their parent values.</p>

<p class="message-info">If no timezone information is given, the returned <code>Datepoint</code> object will use the current timezone.</p>

#### examples

Using the `$datepoint` argument

~~~php
use League\Period\Datepoint;

Datepoint::create('yesterday'); // returns new Datepoint('yesterday')
Datepoint::create('2018');      // returns new Datepoint('@2018')
Datepoint::create(2018);        // returns new Datepoint('@2018')
Datepoint::create(new DateTime('2018-10-15'));  // returns new Datepoint('2018-10-15')
Datepoint::create(new DateTimeImmutable('2018-10-15'));  // returns new Datepoint('2018-10-15')
~~~

## Accessing calendar interval

<p class="message-info">Since <code>version 4.8</code> the <code>$boundaryType</code> argument is added to all the methods described below.</p>

Once you've got a `Datepoint` instantiated object, you can access a set of calendar type interval using the following method signature.

~~~php
public function method(string $boundaryType = Period::INCLUDE_START_EXCLUDE_END): Period
~~~

where `method` is one of the following date time span:

- `Datepoint::getSecond`
- `Datepoint::getMinute`
- `Datepoint::getHour`
- `Datepoint::getDay`
- `Datepoint::getIsoWeek`
- `Datepoint::getMonth`
- `Datepoint::getQuarter`
- `Datepoint::getSemester`
- `Datepoint::getYear`
- `Datepoint::getIsoYear`

For each a these methods a `Period` object is returned with:

- the `Period::INCLUDE_START_EXCLUDE_END` boundary type by default unless changed using the `$boundaryType` argument;
- the starting datepoint represents the beginning of the current datepoint calendar interval;
- the duration associated with the given calendar interval;

#### Examples

~~~php
use League\Period\Datepoint;

$datepoint = new Datepoint('2018-06-18 08:35:25');
$hour = $datepoint->getHour();
// new Period('2018-06-18 08:00:00', '2018-06-18 09:00:00');
$month = $datepoint->getMonth(Period::INCLUDE_ALL);
echo $month->format('Y-m-d');
// [2018-06-01, 2018-07-01 00:00:00];
$month->contains($datepoint); // true
$hour->contains($datepoint);  // true
$month->contains($hour);      // true
~~~

## Relational method against interval

<p class="message-info">Since <code>version 4.5</code></p>

A datepoint can also be evaluated in relation to a given interval.  
The following methods all share the same signature:
 
~~~php
public function method(Period $interval): bool
~~~
 
where `method` is one of the basic relation between a datepoint and an interval.

- `Datepoint::isBefore`
- `Datepoint::bordersOnStart`
- `Datepoint::isStarting`
- `Datepoint::isDuring`
- `Datepoint::isEnding`
- `Datepoint::bordersOnEnd`
- `Datepoint::abuts`
- `Datepoint::isAfter`

#### Examples

~~~php
use League\Period\Datepoint;
use League\Period\Period;

$datepoint = Datepoint::create('2018-01-18 10:00:00');
$datepoint->isBorderingOnStart(
    Period::after($datepoint, '3 minutes', Period::EXCLUDE_START_INCLUDE_END)
); //  true


$datepoint->isBorderingOnStart(
    Period::after($datepoint, '3 minutes', Period::INCLUDE_ALL)
); // false


$datepoint->isAfter(
    Period::before('2018-01-13 23:34:28', '3 minutes', Period::INCLUDE_START_EXCLUDE_END)
);  // true
~~~
