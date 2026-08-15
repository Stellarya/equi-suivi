<?php

namespace App\Controller;

use App\Entity\CompetitionEntry;
use App\Entity\Protocol;
use App\Service\ProtocolService;
use App\Form\ProtocolUploadType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/protocols', name: 'app_protocol_')]
final class ProtocolController extends AppController
{
    public function __construct(
        private readonly ProtocolService $protocolService,
    ) {
    }

    #[Route('/entry/{id}/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request, CompetitionEntry $entry): Response
    {
        $user = $this->getCurrentAppUser();
        $this->protocolService->assertCanManageProtocol($entry, $user);

        $form = $this->createForm(ProtocolUploadType::class, null, [
            'action' => $this->generateUrl('app_protocol_upload', ['id' => $entry->getId()]),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->protocolService->createFromUpload(
                    $entry,
                    $form->get('file')->getData(),
                    $form->get('judgePosition')->getData());
                $this->addFlash('success', 'Protocole importé avec succès');
            } catch (\DomainException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
            
        } else {
            $this->addFlash('danger', 'Import impossible : vérifiez le format du ficiher');
        }

        return $this->redirectToRoute('app_rider_profile', ['_fragment' => 'tab-competitions']);
    }

    #[Route('/{id}/file', name: 'file', methods: ['GET'])]
    public function download(Protocol $protocol): BinaryFileResponse
    {
        $user = $this->getCurrentAppUser();
        $entry = $protocol->getCompetitionEntry();

        if ($entry === null) {
            throw $this->createNotFoundException('Protocole sans participation associée.');
        }

        $this->protocolService->assertCanManageProtocol($entry, $user);

        return $this->file(
            $this->protocolService->getAbsolutePath($protocol),
            $protocol->getFilePath(),
            ResponseHeaderBag::DISPOSITION_INLINE,
        );
    }

}