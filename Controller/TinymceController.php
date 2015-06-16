<?php

namespace Stfalcon\Bundle\TinymceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class TinymceController
 * @package Stfalcon\Bundle\TinymceBundle\Controller
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class TinymceController extends Controller
{
    public function configAction(Request $request)
    {
        if (version_compare(strtolower(Kernel::VERSION), '2.1.0-dev', '<')) {
            if (null !== $session = $request->getSession()) {
                // keep current flashes for one more request
                $session->setFlashes($session->getFlashes());
            }
        } else {
            $session = $request->getSession();
            if ($request->hasPreviousSession() && $session->getFlashBag() instanceof AutoExpireFlashBag) {
                // keep current flashes for one more request if using AutoExpireFlashBag
                $session->getFlashBag()->setAll($session->getFlashBag()->peekAll());
            }
        }

        $config = $this->get('twig.extension.stfalcon_tinymce')->getTinymceConfig();

        $response = new Response($config);
        $response->headers->add(array('Content-Type' => 'application/json'));

        $response
            ->setPublic()
            ->setMaxAge(3600)
        ;

        return $response;
    }
}
