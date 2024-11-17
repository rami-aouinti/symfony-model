<?php

declare(strict_types=1);

namespace App\Platform\Transport\Controller;

use App\Platform\Application\Dto\FeedbackDto;
use App\Platform\Infrastructure\Repository\PageRepository;
use App\Property\Transport\Form\Type\FeedbackType;
use App\User\Transport\Message\SendFeedback;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class PageController
 *
 * @package App\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class PageController extends BaseController
{
    #[Route(path: '/info/{slug}', name: 'page', methods: ['GET|POST'])]
    public function pageShow(
        Request $request,
        MessageBusInterface $messageBus,
        PageRepository $pageRepository
    ): Response {
        $slug = $request->attributes->get('slug');
        $page = $pageRepository->findOneBy(['locale' => $request->getLocale(), 'slug' => $slug])
            ?? $pageRepository->findOneBy(['slug' => $slug]);

        if ($page->getAddContactForm() && '' !== $page->getContactEmailAddress()) {
            $feedback = new FeedbackDto();
            $feedback->setToEmail($page->getContactEmailAddress());

            $form = $this->createForm(FeedbackType::class, $feedback);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $messageBus->dispatch(new SendFeedback($feedback));
                $this->addFlash('success', 'message.was_sent');

                return $this->redirectToRoute('page', ['slug' => $page->getSlug()]);
            }
        }

        return $this->render('page/show.html.twig',
            [
                'site' => $this->site($request),
                'page' => $page,
                'form' => (empty($form) ? [] : $form),
            ]
        );
    }
}
