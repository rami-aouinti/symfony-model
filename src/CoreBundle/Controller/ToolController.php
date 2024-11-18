<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller;

use App\CoreBundle\Tool\ToolChain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @package App\CoreBundle\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[Route('/tool')]
class ToolController extends AbstractController
{
    #[Route(path: '/update', methods: ['GET'])]
    public function profile(ToolChain $toolChain): Response
    {
        $toolChain->createTools();

        return new Response('Updated');
    }
}
