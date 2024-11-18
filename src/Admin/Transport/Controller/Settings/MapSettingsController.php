<?php

declare(strict_types=1);

namespace App\Admin\Transport\Controller\Settings;

use App\Place\Transport\Form\Type\MapSettingsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @package App\Admin\Transport\Controller\Settings
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class MapSettingsController extends AbstractSettingsController
{
    #[Route(path: '/admin/settings/map', name: 'admin_map_settings')]
    public function mapSettings(Request $request): Response
    {
        $form = $this->createForm(MapSettingsType::class, $this->settings);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->updateSettings($form->getNormData());

            return $this->redirectToRoute('admin_map_settings');
        }

        return $this->render('admin/settings/map_settings.html.twig', [
            'site' => $this->settings,
            'form' => $form,
        ]);
    }
}
