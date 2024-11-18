<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller;

use App\CoreBundle\Repository\SysAnnouncementRepository;
use App\CoreBundle\ServiceHelper\AccessUrlHelper;
use App\CoreBundle\ServiceHelper\UserHelper;
use App\CoreBundle\Traits\ControllerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author Julio Montoya <gugli100@gmail.com>.
 */
#[Route('/news')]
class NewsController extends BaseController
{
    use ControllerTrait;

    public function __construct(
        private readonly AccessUrlHelper $accessUrlHelper,
        private readonly UserHelper $userHelper,
    ) {
    }

    #[Route('/list', name: 'news_index', methods: ['GET'])]
    public function index(SysAnnouncementRepository $sysAnnouncementRepository): Response
    {
        $user = $this->userHelper->getCurrent();

        $list = [];
        if ($user !== null) {
            $list = $sysAnnouncementRepository->getAnnouncements(
                $user,
                $this->accessUrlHelper->getCurrent(),
                $this->getRequest()->getLocale()
            );
        }

        return new JsonResponse($list);
    }
}
