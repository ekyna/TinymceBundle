<?php

namespace Stfalcon\Bundle\TinymceBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class RemoveEmptyParagraphTransformer
 * @package Form\DataTransformer
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class RemoveEmptyParagraphTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($html)
    {
        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($html)
    {
        $html = preg_replace('~<p>[&nbsp;|\s]+</p>~', '', $html);
        return preg_replace('~[\r\n]+~', '', $html);
    }
}
