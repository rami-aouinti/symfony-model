<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\LtiBundle\Controller;

use App\CoreBundle\Controller\BaseController;
use App\CoreBundle\Traits\ControllerTrait;
use App\LtiBundle\Entity\ExternalTool;
use App\LtiBundle\Form\ExternalToolType;
use App\LtiBundle\Repository\ExternalToolRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
* Class AdminController
 * @package App\LtiBundle\Controller
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[Route(path: '/admin/lti')]
class AdminController extends BaseController
{
    use ControllerTrait;

    public function __construct(
        private ManagerRegistry $managerRegistry,
        private ExternalToolRepository $externalToolRepository
    ) {
    }

    #[Route(path: '/', name: 'chamilo_lti_admin')]
    public function admin(): Response
    {
        $tools = $this->externalToolRepository->findAll();

        return $this->render('@ChamiloCore/Lti/admin.html.twig', [
            'tools' => $tools,
        ]);
    }

    #[Route(path: '/add', name: 'chamilo_lti_admin_add')]
    public function adminAdd(Request $request): Response
    {
        $form = $this->createForm(ExternalToolType::class, new ExternalTool());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ExternalTool $tool */
            $tool = $form->getData();

            $this->externalToolRepository->create($tool);

            $this->addFlash('success', $this->trans('External tool added'));

            return $this->redirectToRoute('chamilo_lti_admin');
        }

        return $this->render(
            '@ChamiloCore/Lti/admin_form.html.twig',
            [
                'form' => $form,
            ]
        );
    }

    #[Route(path: '/edit/{toolId}', name: 'chamilo_lti_admin_edit', requirements: [
        'toolId' => '\d+',
    ])]
    public function adminEdit(int $toolId, Request $request): Response
    {
        $em = $this->managerRegistry
            ->getManager()
        ;

        /** @var ExternalTool $tool */
        $tool = $em->find(ExternalTool::class, $toolId);

        if (empty($tool)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ExternalToolType::class, $tool);
        $form->get('shareName')->setData($tool->isSharingName());
        $form->get('shareEmail')->setData($tool->isSharingEmail());
        $form->get('sharePicture')->setData($tool->isSharingPicture());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ExternalTool $tool */
            $tool = $form->getData();

            $em->persist($tool);
            $em->flush();

            $this->addFlash('success', $this->trans('External tool edited'));

            return $this->redirectToRoute('chamilo_lti_admin');
        }

        return $this->render(
            '@ChamiloCore/Lti/admin_form.html.twig',
            [
                'form' => $form,
            ]
        );
    }

    #[Route(path: '/delete/{toolId}', name: 'chamilo_lti_admin_delete', requirements: [
        'toolId' => '\d+',
    ])]
    public function adminDelete(int $toolId): Response
    {
        $em = $this->managerRegistry->getManager();

        /** @var ExternalTool $tool */
        $tool = $em->find(ExternalTool::class, $toolId);

        if (empty($tool)) {
            throw $this->createNotFoundException();
        }

        $em->remove($tool);
        $em->flush();

        $this->addFlash('success', $this->trans('External tool deleted'));

        return $this->redirectToRoute('chamilo_lti_admin');
    }
}
