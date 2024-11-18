<?php

declare(strict_types=1);

namespace App\MyBundle\src\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @package App\MyBundle\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class MyController
{
    #[Route('/my-bundle', name: 'my_bundle_home')]
    public function index(): Response
    {
        return new Response('Hello from MyBundle!');
    }
}
