<?php


namespace Blaga\DateFormatBundle\Twig\Extension;

use Blaga\DateFormatBundle\DateFormatProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateFormatExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('date', [$this, 'formatDate']),
        ];
    }

    public function formatDate($date) {
        $dateFormat = new DateFormatProvider();

        return date_format($date, $dateFormat);
    }

    public function __toString()
    {
        return 'date';
    }
}