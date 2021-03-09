<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Period;

use DateInterval;
use DateTimeImmutable;
use TypeError;
use function filter_var;
use function gettype;
use function is_string;
use function method_exists;
use function preg_match;
use function property_exists;
use function sprintf;
use function str_pad;
use const FILTER_VALIDATE_FLOAT;

/**
 * League Period Duration.
 *
 * @package League.period
 * @author  Ignace Nyamagana Butera <nyamsprod@gmail.com>
 * @since   4.2.0
 */
final class Duration extends DateInterval
{
    private const REGEXP_DATEINTERVAL_WORD_SPEC = '/^P\S*$/';

    private const REGEXP_DATEINTERVAL_SPEC = '@^P
        (?!$)                             # making sure there something after the interval delimiter
        (?:(\d+Y)?(\d+M)?(\d+W)?(\d+D)?)? # day, week, month, year part
        (?:T                              # interval time delimiter
            (?!$)                         # making sure there something after the interval time delimiter
            (?:\d+H)?(?:\d+M)?(?:\d+S)?   # hour, minute, second part
        )?
    $@x';

    private const REGEXP_MICROSECONDS_INTERVAL_SPEC = '@^(?<interval>.*)(\.|,)(?<fraction>\d{1,6})S$@';

    private const REGEXP_MICROSECONDS_DATE_SPEC = '@^(?<interval>.*)(\.)(?<fraction>\d{1,6})$@';

    private const REGEXP_CHRONO_FORMAT = '@^
        (?<sign>\+|-)?                  # optional sign
        ((?<hour>\d+):)?                # optional hour
        ((?<minute>\d+):)(?<second>\d+) # required minute and second
        (\.(?<fraction>\d{1,6}))?       # optional fraction
    $@x';

    private const REGEXP_TIME_FORMAT = '@^
        (?<sign>\+|-)?                               # optional sign
        (?<hour>\d+)(:(?<minute>\d+))                # required hour and minute
        (:(?<second>\d+)(\.(?<fraction>\d{1,6}))?)?  # optional second and fraction
    $@x';

    /**
     * New instance.
     *
     * Returns a new instance from an Interval specification
     */
    public function __construct(string $interval_spec)
    {
        if (1 === preg_match(self::REGEXP_MICROSECONDS_INTERVAL_SPEC, $interval_spec, $matches)) {
            parent::__construct($matches['interval'].'S');
            $this->f = (float) str_pad($matches['fraction'], 6, '0') / 1e6;
            return;
        }

        if (1 === preg_match(self::REGEXP_MICROSECONDS_DATE_SPEC, $interval_spec, $matches)) {
            parent::__construct($matches['interval']);
            $this->f = (float) str_pad($matches['fraction'], 6, '0') / 1e6;
            return;
        }

        parent::__construct($interval_spec);
    }

    /**
     * Returns a continuous portion of time between two datepoints expressed as a DateInterval object.
     *
     * The duration can be
     * <ul>
     * <li>an Period object</li>
     * <li>a DateInterval object</li>
     * <li>an integer interpreted as the duration expressed in seconds.</li>
     * <li>a string parsable by DateInterval::createFromDateString</li>
     * </ul>
     *
     * @param mixed $duration a continuous portion of time
     *
     * @throws TypeError if the duration type is not a supported
     */
    public static function create($duration): self
    {
        if ($duration instanceof Period) {
            return self::createFromDateInterval($duration->getDateInterval());
        }

        if ($duration instanceof DateInterval) {
            return self::createFromDateInterval($duration);
        }

        $seconds = filter_var($duration, FILTER_VALIDATE_FLOAT);
        if (false !== $seconds) {
            return self::createFromSeconds($seconds);
        }

        if (!is_string($duration) && !method_exists($duration, '__toString')) {
            throw new TypeError(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($duration)));
        }

        $duration = (string) $duration;

        if (1 === preg_match(self::REGEXP_CHRONO_FORMAT, $duration)) {
            return self::createFromChronoString($duration);
        }

        if (1 === preg_match(self::REGEXP_DATEINTERVAL_WORD_SPEC, $duration)) {
            if (1 === preg_match(self::REGEXP_DATEINTERVAL_SPEC, $duration)) {
                return new self($duration);
            }

            throw new Exception(sprintf('Unknown or bad format (%s)', $duration));
        }

        try {
            $instance = self::createFromDateString($duration);
        } catch (\Exception $exception) {
            throw new Exception(sprintf('Unknown or bad format (%s)', $duration), 0, $exception);
        }

        if (false !== $instance) {
            return $instance;
        }

        throw new Exception(sprintf('Unknown or bad format (%s)', $duration));
    }

    /**
     * Creates a new instance from a DateInterval object.
     *
     * the second value will be overflow up to the hour time unit.
     */
    public static function createFromDateInterval(DateInterval $duration): self
    {
        $new = new self('PT0S');
        foreach ($duration as $name => $value) {
            if (property_exists($new, $name)) {
                $new->$name = $value;
            }
        }

        return $new;
    }

    /**
     * Creates a new instance from a seconds.
     *
     * the second value will be overflow up to the hour time unit.
     */
    public static function createFromSeconds(float $seconds): self
    {
        $invert = 0 > $seconds;
        if ($invert) {
            $seconds = $seconds * -1;
        }

        $secondsInt = (int) $seconds;
        $fraction = (int) (($seconds - $secondsInt) * 1e6);
        $minute = intdiv($secondsInt, 60);
        $secondsInt = $secondsInt - ($minute * 60);
        $hour = intdiv($minute, 60);
        $minute = $minute - ($hour * 60);

        return self::createFromTimeUnits([
            'hour' => (string) $hour,
            'minute' => (string) $minute,
            'second' => (string) $secondsInt,
            'fraction' => (string) $fraction,
            'sign' => $invert ? '-' : '+',
        ]);
    }

    /**
     * Creates a new instance from a timer string representation.
     *
     * @throws Exception
     */
    public static function createFromChronoString(string $duration): self
    {
        if (1 !== preg_match(self::REGEXP_CHRONO_FORMAT, $duration, $units)) {
            throw new Exception(sprintf('Unknown or bad format (%s)', $duration));
        }

        if ('' === $units['hour']) {
            $units['hour'] = '0';
        }

        return self::createFromTimeUnits($units);
    }

    /**
     * Creates a new instance from a time string representation following RDBMS specification.
     *
     * @throws Exception
     */
    public static function createFromTimeString(string $duration): self
    {
        if (1 !== preg_match(self::REGEXP_TIME_FORMAT, $duration, $units)) {
            throw new Exception(sprintf('Unknown or bad format (%s)', $duration));
        }

        return self::createFromTimeUnits($units);
    }

    /**
     * Creates an instance from DateInterval units.
     *
     * @param array<string,string> $units
     */
    private static function createFromTimeUnits(array $units): self
    {
        $units = $units + ['hour' => '0', 'minute' => '0', 'second' => '0', 'fraction' => '0', 'sign' => '+'];

        $units['fraction'] = str_pad($units['fraction'] ?? '000000', 6, '0');

        $expression = $units['hour'].' hours '
            .$units['minute'].' minutes '
            .$units['second'].' seconds '
            .$units['fraction'].' microseconds';

        /** @var Duration $instance */
        $instance = self::createFromDateString($expression);
        if ('-' === $units['sign']) {
            $instance->invert = 1;
        }

        return $instance;
    }

    /**
     * @inheritDoc
     *
     * @param mixed $duration a date with relative parts
     *
     * @return self|false
     */
    public static function createFromDateString($duration)
    {
        $duration = parent::createFromDateString($duration);
        if (false === $duration) {
            return false;
        }

        $new = new self('PT0S');
        foreach ($duration as $name => $value) {
            $new->$name = $value;
        }

        return $new;
    }

    /**
     * Returns a new instance with recalculate properties according to a given datepoint.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the time and date segments recalculate to remove
     * carry over points.
     *
     * @param mixed $reference_date a reference datepoint {@see \League\Period\Datepoint::create}
     */
    public function adjustedTo($reference_date): self
    {
        if (!$reference_date instanceof DateTimeImmutable) {
            $reference_date = Datepoint::create($reference_date);
        }

        return self::create($reference_date->diff($reference_date->add($this)));
    }
}
