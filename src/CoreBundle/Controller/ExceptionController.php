<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

class ExceptionController extends AbstractController
{
    public function show(Exception $exception): Response
    {
        if ((string)$this->getParameter('app_env') === 'dev') {
            throw new HttpException($exception->getCode(), $exception->getMessage());
        }

        $showException = true;
        // $name = $showException ? 'exception' : 'error';
        $name = 'exception';
        $code = $exception->getCode();
        $format = 'html';
        $loader = $this->container->get('twig')->getLoader();

        $templateToLoad = \sprintf('@ChamiloCore/Exception/%s.html.twig', 'exception_full');

        // when not in debug, try to find a template for the specific HTTP status code and format
        $template = \sprintf('@ChamiloCore/Exception/%s%s.%s.twig', $name, $code, $format);
        if ($loader->exists($template)) {
            $templateToLoad = $template;
        }

        // try to find a template for the given format
        $template = \sprintf('@ChamiloCore/Exception/%s.%s.twig', $name, $format);
        if ($loader->exists($template)) {
            $templateToLoad = $template;
        }

        // default to a generic HTML exception
        // $request->setRequestFormat('html');
        // $template = sprintf('@ChamiloCore/Exception/%s.html.twig', $showException ? 'exception_full' : $name);

        return $this->render($templateToLoad, [
            'exception' => $exception,
        ]);
    }

    #[Route(path: '/error')]
    public function error(Request $request): Response
    {
        $message = $request->getSession()->get('error_message', '');
        $exception = new FlattenException();
        $exception->setCode(500);

        $exception->setMessage($message);

        $showException = true;
        // $name = $showException ? 'exception' : 'error';
        $name = 'exception';
        $code = $exception->getCode();
        $format = 'html';
        $loader = $this->container->get('twig')->getLoader();

        $templateToLoad = \sprintf('@ChamiloCore/Exception/%s.html.twig', 'exception_full');

        // when not in debug, try to find a template for the specific HTTP status code and format
        // if (!$showException) {
        $template = \sprintf('@ChamiloCore/Exception/%s%s.%s.twig', $name, $code, $format);
        if ($loader->exists($template)) {
            $templateToLoad = $template;
        }
        // }

        // try to find a template for the given format
        $template = \sprintf('@ChamiloCore/Exception/%s.%s.twig', $name, $format);
        if ($loader->exists($template)) {
            $templateToLoad = $template;
        }

        // default to a generic HTML exception
        // $request->setRequestFormat('html');

        return $this->render($templateToLoad, [
            'exception' => $exception,
        ]);
    }
}
