<?php


namespace SearchBundle\Twig\Extension;

/**
 * The class is requiered to check the instance of results returned from database.
 *
 * Class InstanceOfExtension
 * @package SearchBundle\Twig\Extension
 */
class InstanceOfExtension extends \Twig_Extension
{
    public function getTests() {
        return array(
            new \Twig_SimpleTest('instanceof', array($this, 'isInstanceOf')),
        );
    }

    /**
     * @param $var
     * @param $instance
     * @return bool
     *
     * @throws \ReflectionException
     */
    public function isInstanceOf($var, $instance) {
        $reflexionClass = new \ReflectionClass($instance);
        return $reflexionClass->isInstance($var);
    }
}