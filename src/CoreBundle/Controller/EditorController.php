<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller;

use App\CoreBundle\Component\Editor\CkEditor\CkEditor;
use App\CoreBundle\Traits\ControllerTrait;
use App\CoreBundle\Traits\CourseControllerTrait;
use App\CoreBundle\Traits\ResourceControllerTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @package App\CoreBundle\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[Route('/editor')]
class EditorController extends BaseController
{
    use ControllerTrait;
    use CourseControllerTrait;
    use ResourceControllerTrait;

    /**
     * Get templates (left column when creating a document).
     */
    #[Route(path: '/templates', name: 'editor_templates', methods: ['GET'])]
    public function editorTemplates(TranslatorInterface $translator, RouterInterface $router): Response
    {
        $editor = new CkEditor(
            $translator,
            $router
        );
        $templates = $editor->simpleFormatTemplates();

        return $this->render(
            '@ChamiloCore/Editor/templates.html.twig',
            [
                'templates' => $templates,
            ]
        );
    }
}
