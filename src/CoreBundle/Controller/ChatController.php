<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\CoreBundle\Controller;

use App\CoreBundle\Repository\ResourceNodeRepository;
use App\CoreBundle\Traits\ControllerTrait;
use App\CoreBundle\Traits\CourseControllerTrait;
use App\CoreBundle\Traits\ResourceControllerTrait;
use App\CourseBundle\Controller\CourseControllerInterface;
use App\CourseBundle\Repository\CChatConversationRepository;
use CourseChatUtils;
use Doctrine\DBAL\Exception;
use Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @package App\CoreBundle\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class ChatController extends AbstractResourceController implements CourseControllerInterface
{
    use ControllerTrait;
    use CourseControllerTrait;
    use ResourceControllerTrait;

    /**
     * @throws Exception
     */
    #[Route(path: '/resources/chat/', name: 'chat_home', options: [
        'expose' => true,
    ])]
    public function index(): Response
    {
        Event::event_access_tool(TOOL_CHAT);
        $logInfo = [
            'tool' => TOOL_CHAT,
            'action' => 'start',
            'action_details' => 'start-chat',
        ];
        Event::registerLog($logInfo);

        return $this->render(
            '@ChamiloCore/Chat/chat.html.twig',
            [
                'restrict_to_coach' => (api_get_setting('chat.course_chat_restrict_to_coach') === 'true'),
                'user' => api_get_user_info(),
                'emoji_smile' => '<span>&#128522;</span>',
                'course_url_params' => api_get_cidreq(),
            ]
        );
    }

    #[Route(path: '/resources/chat/conversations/', name: 'chat_ajax', options: [
        'expose' => true,
    ])]
    public function ajax(Request $request, ResourceNodeRepository $repo): Response
    {
        if (!api_protect_course_script(false)) {
            exit;
        }

        /** @var CChatConversationRepository $resourceRepo */
        $resourceRepo = $this->getRepository('chat', 'conversations');

        $courseId = api_get_course_int_id();
        $userId = api_get_user_id();
        $sessionId = api_get_session_id();
        $groupId = api_get_group_id();
        $json = [
            'status' => false,
        ];
        $parentResourceNode = $this->getParentResourceNode($request);

        $courseChatUtils = new CourseChatUtils(
            $courseId,
            $userId,
            $sessionId,
            $groupId,
            $parentResourceNode,
            $resourceRepo
        );

        $action = $request->get('action');

        switch ($action) {
            case 'chat_logout':
                $logInfo = [
                    'tool' => TOOL_CHAT,
                    'action' => 'exit',
                    'action_details' => 'exit-chat',
                ];
                Event::registerLog($logInfo);

                break;
            case 'track':
                $courseChatUtils->keepUserAsConnected();
                $courseChatUtils->disconnectInactiveUsers();

                $friend = isset($_REQUEST['friend']) ? (int)$_REQUEST['friend'] : 0;
                // $filePath = $courseChatUtils->getFileName(true, $friend);
                // $newFileSize = file_exists($filePath) ? filesize($filePath) : 0;
                // $oldFileSize = isset($_GET['size']) ? (int) $_GET['size'] : -1;
                $newUsersOnline = $courseChatUtils->countUsersOnline();
                $oldUsersOnline = isset($_GET['users_online']) ? (int)$_GET['users_online'] : 0;

                $json = [
                    'status' => true,
                    'data' => [
                        // 'oldFileSize' => file_exists($filePath) ? filesize($filePath) : 0,
                        'oldFileSize' => false,
                        'history' => $courseChatUtils->readMessages(false, $friend),
                        'usersOnline' => $newUsersOnline,
                        'userList' => $newUsersOnline !== $oldUsersOnline ? $courseChatUtils->listUsersOnline() : null,
                        'currentFriend' => $friend,
                    ],
                ];

                break;
            case 'preview':
                $json = [
                    'status' => true,
                    'data' => [
                        'message' => $courseChatUtils->prepareMessage($_REQUEST['message']),
                    ],
                ];

                break;
            case 'reset':
                $friend = isset($_REQUEST['friend']) ? (int)$_REQUEST['friend'] : 0;

                $json = [
                    'status' => true,
                    'data' => $courseChatUtils->readMessages(true, $friend),
                ];

                break;
            case 'write':
                $friend = isset($_REQUEST['friend']) ? (int)$_REQUEST['friend'] : 0;
                $status = $courseChatUtils->saveMessage($_REQUEST['message'], $friend);

                $json = [
                    'status' => $status,
                    'data' => [
                        'writed' => $status,
                    ],
                ];

                break;
        }

        return new JsonResponse($json);
    }
}
