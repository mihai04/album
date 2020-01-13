<?php


namespace TestBundle\Service;

use Blaga\DateFormatBundle\DateFormatProvider;

class CustomDateFormatProvider extends DateFormatProvider
{
    public function getDateFormat()
    {
        return "Y/m/d H:i:s";
    }
}