<?php

namespace Stfalcon\Bundle\TinymceBundle\Form\Type;

use Stfalcon\Bundle\TinymceBundle\Form\DataTransformer\RemoveEmptyParagraphTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TinymceType
 * @package Stfalcon\Bundle\TinymceBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class TinymceType extends AbstractType
{
    /**
     * @var array
     */
    private $themes;

    /**
     * Constructor.
     *
     * @param array $themes
     */
    public function __construct(array $themes)
    {
        $this->themes = $themes;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options = array())
    {
        $builder->addModelTransformer(new RemoveEmptyParagraphTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $attrNormalizer = function (Options $options, $value) {
            $theme = isset($options['theme'])
                     && is_string($options['theme'])
                     && in_array($options['theme'], $this->themes)
                     ? $options['theme'] : 'simple';

            if (array_key_exists('class', $value) && 0 < strlen($value['class'])) {
                $value['class'] .= ' tinymce';
            } else {
                $value['class'] = 'tinymce';
            }

            $value['data-theme'] = $theme;

            return $value;
        };

        $resolver
            ->setDefaults(array(
                'theme' => 'simple',
            ))
            ->setAllowedTypes(array(
                'theme' => 'string'
            ))
            ->setNormalizers(array(
                'attr' => $attrNormalizer,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'textarea';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tinymce';
    }
}
