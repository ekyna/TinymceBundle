<?php
namespace Stfalcon\Bundle\TinymceBundle\Twig\Extension;

use Stfalcon\Bundle\TinymceBundle\Helper\LocaleHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Twig Extension for TinyMce support.
 *
 * @author naydav <web@naydav.com>
 */
class StfalconTinymceExtension extends \Twig_Extension
{
    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Asset Base Url
     * Used to over ride the asset base url (to not use CDN for instance)
     *
     * @var String
     */
    protected $baseUrl;

    /**
     * Initialize tinymce helper
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Gets a service.
     *
     * @param string $id The service identifier
     *
     * @return object The associated service
     */
    public function getService($id)
    {
        return $this->container->get($id);
    }

    /**
     * Get parameters from the service container
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'tinymce_init' => new \Twig_Function_Method($this, 'tinymceInit', array('is_safe' => array('html'))),
            'get_tinymce_config' => new \Twig_Function_Method($this, 'getTinymceConfig'),
        );
    }

    /**
     * Returns the tinymce configuration.
     *
     * @param array $options
     * @param bool $jsonEncode
     * @return array|string
     */
    public function getTinymceConfig($options = array(), $theme = null, $jsonEncode = true)
    {
        $config = $this->getParameter('stfalcon_tinymce.config');
        $config = array_merge_recursive($config, $options);

        $this->baseUrl = (!isset($config['base_url']) ? null : rtrim($config['base_url'], '/'));

        // Get local button's image
        foreach ($config['tinymce_buttons'] as &$customButton) {
            if ($customButton['image']) {
                $customButton['image'] = $this->getAssetsUrl($customButton['image']);
            } else {
                unset($customButton['image']);
            }

            if ($customButton['icon']) {
                $customButton['icon'] = $this->getAssetsUrl($customButton['icon']);
            } else {
                unset($customButton['icon']);
            }
        }

        // Update URL to external plugins
        foreach ($config['external_plugins'] as &$extPlugin) {
            $extPlugin['url'] = $this->getAssetsUrl($extPlugin['url']);
        }

        // If the language is not set in the config...
        if (!isset($config['language']) || empty($config['language'])) {
            // get it from the request
            $config['language'] = $this->getService('request')->getLocale();
        }

        $config['language'] = LocaleHelper::getLanguage($config['language']);

        $langDirectory = __DIR__ . '/../../Resources/public/vendor/tinymce-langs/';

        // A language code coming from the locale may not match an existing language file
        if (!file_exists($langDirectory . $config['language'] . '.js')) {
            unset($config['language']);
        }

        if (isset($config['language']) && $config['language']) {
            $languageUrl = $this->getAssetsUrl(
                '/bundles/stfalcontinymce/vendor/tinymce-langs/' . $config['language'] . '.js'
            );
            // TinyMCE does not allow to set different languages to each instance
            foreach ($config['theme'] as $themeName => $themeOptions) {
                $config['theme'][$themeName]['language'] = $config['language'];
                $config['theme'][$themeName]['language_url'] = $languageUrl;
            }
            $config['language_url'] = $languageUrl;
        }

        if (isset($config['theme']) && $config['theme']) {
            // Parse the content_css of each theme so we can use 'asset[path/to/asset]' in there
            foreach ($config['theme'] as $themeName => $themeOptions) {
                if (isset($themeOptions['content_css'])) {
                    // As there may be multiple CSS Files specified we need to parse each of them individually
                    $cssFiles = is_array($themeOptions['content_css'])
                        ? $themeOptions['content_css']
                        : explode(',', $themeOptions['content_css']);
                    foreach ($cssFiles as $idx => $file) {
                        // we trim to be sure we get the file without spaces.
                        $cssFiles[$idx] = $this->getAssetsUrl(trim($file));
                    }
                    $config['theme'][$themeName]['content_css'] = array_values($cssFiles);
                }
            }
        }

        if (0 < strlen($theme) && array_key_exists($theme, $config['theme'])) {
            $config = $config['theme'][$theme];
        }

        if ($jsonEncode) {
            $config = preg_replace(
                '/"file_browser_callback":"([^"]+)"\s*/', 'file_browser_callback:$1',
                json_encode($config)
            );
        }

        return $config;
    }

    /**
     * TinyMce initializations
     *
     * @param array $options
     * @return string
     */
    public function tinymceInit($options = array())
    {
        $config = $this->getTinymceConfig($options, null, false);

        // Get path to tinymce script for the jQuery version of the editor
        if ($config['tinymce_jquery']) {
            $config['jquery_script_url'] = $this->getAssetsUrl(
                '/js/tinymce/jquery.tinymce.min.js'
            );
        }

        return $this->getService('templating')->render('StfalconTinymceBundle:Script:init.html.twig', array(
            'tinymce_config' => preg_replace(
                '/"file_browser_callback":"([^"]+)"\s*/', 'file_browser_callback:$1',
                json_encode($config)
            ),
            'include_jquery' => $config['include_jquery'],
            'tinymce_jquery' => $config['tinymce_jquery'],
            'base_url'       => $this->baseUrl,
            'tinymce_url'    => trim($config['tinymce_url'], '/'),
        ));
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'stfalcon_tinymce';
    }

    /**
     * Get url from config string
     *
     * @param string $inputUrl
     *
     * @return string
     */
    protected function getAssetsUrl($inputUrl)
    {
        /** @var $assets \Symfony\Component\Templating\Helper\CoreAssetsHelper */
        $assets = $this->getService('templating.helper.assets');

        $url = preg_replace('/^asset\[(.+)\]$/i', '$1', $inputUrl);

        if ($inputUrl !== $url) {
            return $assets->getUrl($this->baseUrl . $url);
        }

        return $inputUrl;
    }
}
