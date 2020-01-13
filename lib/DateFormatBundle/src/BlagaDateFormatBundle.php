<?php


namespace Blaga\DateFormatBundle;


use Blaga\DateFormatBundle\DependencyInjection\BlagaDateFormatExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BlagaDateFormatBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new BlagaDateFormatExtension();
        }

        return $this->extension;
    }
}