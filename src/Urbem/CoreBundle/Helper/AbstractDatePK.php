<?php

namespace Urbem\CoreBundle\Helper;

use Doctrine\DBAL\Types\ConversionException;

abstract class AbstractDatePK extends \DateTime implements DatePKInterface
{
    const FORMAT = null;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->format(static::FORMAT);
    }

    /**
     * @param $format
     * @param $time
     * @param \DateTimeZone|null $timezone
     * @return null
     * @throws ConversionException
     */
    public static function toPHPValue($format, $time, \DateTimeZone $timezone = null)
    {
        if (null === $time) {
            return null;
        }

        try {
            $timezone = null === $timezone ? new \DateTimeZone(date_default_timezone_get()) : $timezone;

            if (false === parent::createFromFormat($format, $time, $timezone)) {
                throw new \InvalidArgumentException("Invalid date format");
            }

            $date = (new static($time))->setTimezone($timezone);
        } catch (\Exception $e) {
            throw ConversionException::conversionFailedFormat($time, static::class, $format);
        }

        return $date;
    }
}
