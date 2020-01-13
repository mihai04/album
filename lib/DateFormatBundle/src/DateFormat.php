<?php

namespace Blaga\DateFormatBundle;

class DateFormat
{
    /**
     * @var DateFormatProvider
     */
    private $dateFormatProvider;

    public function __construct(DateFormatProvider $dateFormatProvider)
    {
        $this->dateFormatProvider = $dateFormatProvider;
    }

    public function getDateFormat() {
        $this->dateFormatProvider->getDateFormat();
    }
}