<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace App\LtiBundle\Controller;

use App\CoreBundle\Controller\BaseController;
use App\LtiBundle\Component\OutcomeDeleteRequest;
use App\LtiBundle\Component\OutcomeReadRequest;
use App\LtiBundle\Component\OutcomeReplaceRequest;
use App\LtiBundle\Component\OutcomeResponse;
use App\LtiBundle\Component\OutcomeUnsupportedRequest;
use App\LtiBundle\Entity\ExternalTool;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use OAuthUtil;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @package App\LtiBundle\Controller
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
class ServiceController extends BaseController
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/lti/os', name: 'chamilo_lti_os')]
    public function outcomeService(Request $request): Response
    {
        $em = $this->managerRegistry->getManager();
        $toolRepo = $em->getRepository(ExternalTool::class);

        $headers = $request->headers;

        if (empty($headers->get('authorization'))) {
            throw $this->createAccessDeniedException();
        }

        $authParams = OAuthUtil::split_header($headers['Authorization']);

        if (empty($authParams) || empty($authParams['oauth_consumer_key']) || empty($authParams['oauth_signature'])) {
            throw $this->createAccessDeniedException();
        }

        $course = $this->getCourse();
        $tools = $toolRepo->findBy([
            'consumerKey' => $authParams['oauth_consumer_key'],
        ]);
        $url = $this->generateUrl('chamilo_lti_os', [
            'code' => $course->getCode(),
        ]);

        $toolIsFound = false;

        /** @var ExternalTool $tool */
        foreach ($tools as $tool) {
            $signatureIsValid = $this->compareRequestSignature(
                $url,
                $authParams['oauth_consumer_key'],
                $authParams['oauth_signature'],
                $tool
            );

            if ($signatureIsValid) {
                $toolIsFound = true;

                break;
            }
        }

        if (!$toolIsFound) {
            throw $this->createNotFoundException('External tool not found. Signature is not valid');
        }

        $body = file_get_contents('php://input');
        $bodyHash = base64_encode(sha1($body, true));

        if ($bodyHash !== $authParams['oauth_body_hash']) {
            throw $this->createAccessDeniedException('Request is not valid.');
        }

        $process = $this->processServiceRequest();

        $response = new Response($process);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }

    /**
     * @throws Exception
     */
    private function processServiceRequest(): ?OutcomeResponse
    {
        $requestContent = file_get_contents('php://input');

        if (empty($requestContent)) {
            return null;
        }

        $xml = new SimpleXMLElement($requestContent);

        if (empty($xml)) {
            return null;
        }

        $bodyChildren = $xml->imsx_POXBody->children();

        if (empty($bodyChildren)) {
            return null;
        }

        $name = $bodyChildren->getName();

        switch ($name) {
            case 'replaceResultRequest':
                $serviceRequest = new OutcomeReplaceRequest($xml);

                break;
            case 'readResultRequest':
                $serviceRequest = new OutcomeReadRequest($xml);

                break;
            case 'deleteResultRequest':
                $serviceRequest = new OutcomeDeleteRequest($xml);

                break;
            default:
                $name = str_replace(['ResultRequest', 'Request'], '', $name);

                $serviceRequest = new OutcomeUnsupportedRequest($xml, $name);

                break;
        }

        $serviceRequest->setEntityManager($this->managerRegistry->getManager());
        $serviceRequest->setTranslator($this->translator);

        return $serviceRequest->process();
    }
}
