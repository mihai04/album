<?php


namespace Blaga\DateFormatBundle;

/**
 * Class DateFormatProvider
 * @package Blaga\DateFormatBundle
 */
class DateFormatProvider
{
    const DEFAULT_DATE_FORMAT = "Y/m/d";

    /**
     * @return string
     */
    public function getDateFormatProvider() {
        return self::DEFAULT_DATE_FORMAT;
    }
}