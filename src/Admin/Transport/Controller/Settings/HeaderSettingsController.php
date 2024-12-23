<?php

declare(strict_types=1);

namespace App\Admin\Transport\Controller\Settings;

use App\Property\Transport\Form\Type\FilterSettingsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @package App\Admin\Transport\Controller\Settings
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class HeaderSettingsController extends AbstractSettingsController
{
    #[Route(path: '/admin/settings/header', name: 'admin_header_settings')]
    public function changeHeaderImage(Request $request): Response
    {
        $settings = $this->settings;

        $form = $this->createForm(FilterSettingsType::class, $settings);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->updateSettings($form->getNormData());

            return $this->redirectToRoute('admin_header_settings');
        }

        return $this->render('admin/settings/header_settings.html.twig', [
            'site' => $settings,
            'header_image' => $settings['header_image'],
            'logo_image' => $settings['logo_image'],
            'form' => $form,
        ]);
    }

    #[Route(path: '/admin/setting/delete_header_image', name: 'admin_setting_delete_header_image', methods: ['POST'])]
    public function deleteHeaderImage(Request $request): Response
    {
        // Reset a header image to the default image.
        $this->service->resetImage('header_image', $request);

        return $this->redirectToRoute('admin_header_settings');
    }

    #[Route(path: '/admin/setting/delete_logo_image', name: 'admin_setting_delete_logo_image', methods: ['POST'])]
    public function deleteLogoImage(Request $request): Response
    {
        // Reset a header image to the default image.
        $this->service->resetImage('logo_image', $request);

        return $this->redirectToRoute('admin_header_settings');
    }
}
