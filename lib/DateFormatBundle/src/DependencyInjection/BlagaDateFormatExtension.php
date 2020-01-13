<?php


namespace Blaga\DateFormatBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class KnpUDateFormatExtension
 * @package KnpU\DateFormatBundle\DependencyInjection
 */
class BlagaDateFormatExtension extends Extension
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definitions = $container->getDefinition('blaga_date_format.date_format');
        if (null !== $config['date_format_provider']) {
            // override alias to point to client services id.
            $definitions->setArgument(0, new Reference($config['date_format_provider']));
        }
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return 'blaga_date_format';
    }
}