<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Yuzu\PipelineBundle\DependencyInjection;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class YuzuPipelineExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @param array $config    An array of configuration values
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        // Base services override
        $loader->load('services.yml');
    }

    /**
     * Loads DemoBundle configuration.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $legacyConfigFile = __DIR__ . '/../Resources/config/legacy_settings.yml';
        $config = Yaml::parse(file_get_contents($legacyConfigFile));
        $container->prependExtensionConfig('ez_publish_legacy', $config);
        $container->addResource(new FileResource($legacyConfigFile));

        $configFile = __DIR__ . '/../Resources/config/override.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($configFile));
    }
}
