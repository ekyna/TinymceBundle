<?php

namespace Stfalcon\Bundle\TinymceBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * StfalconTinymceExtension
 */
class StfalconTinymceExtension extends Extension
{
    /**
     * Loads the StfalconTinymce configuration.
     *
     * @param array            $configs   An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Get default configuration of the bundle
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (empty($config['theme'])) {
            $config['theme'] = array(
                'simple' => array()
            );
        } else {
            foreach ($config['theme'] as &$bundleTheme) {
                // Quick fix for the removed obsolete themes
                if (isset($bundleTheme['theme']) && in_array($bundleTheme['theme'], array('advanced', 'simple'))) {
                    $bundleTheme['theme'] = 'modern';
                }
                unset($bundleTheme);
            }
        }

        // Install command directories
        $container->setParameter('stfalcon_tinymce.tinymce_source_dir', $config['tinymce_source_dir']);
        unset($config['tinymce_source_dir']);
        $container->setParameter('stfalcon_tinymce.tinymce_target_dir', $config['tinymce_target_dir']);
        unset($config['tinymce_target_dir']);

        $container->setParameter('stfalcon_tinymce.config', $config);
        $container->setParameter('stfalcon_tinymce.themes', array_keys($config['theme']));

        // load dependency injection config
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('service.xml');
    }

    /**
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     */
    public function getAlias()
    {
        return 'stfalcon_tinymce';
    }
}
