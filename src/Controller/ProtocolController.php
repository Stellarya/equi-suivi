<?php

namespace App\Controller;

use App\Entity\CompetitionEntry;
use App\Entity\Protocol;
use App\Form\ProtocolReviewType;
use App\Service\ProtocolService;
use App\Form\ProtocolUploadType;
use App\Service\ProtocolAnalysisApplier;
use Doctrine\ORM\EntityManagerInterface;
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
        private readonly ProtocolAnalysisApplier $applier,
        private readonly EntityManagerInterface $em
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

    #[Route('/{id}', name: 'show', methods: ['GET', 'POST'])]
    public function show(Request $request, Protocol $protocol): Response
    {
        $user = $this->getCurrentAppUser();
        $entry = $protocol->getCompetitionEntry();

        if ($entry === null) {
            throw $this->createNotFoundException('Protocole sans participation associée.');
        }

        $this->protocolService->assertCanManageProtocol($entry, $user);

        $form = $this->createForm(ProtocolReviewType::class, $protocol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $complete = $this->applier->recalculate($protocol);

            $protocol->setStatus(
                $complete ? Protocol::STATUS_ANALYZED : Protocol::STATUS_NEEDS_REVIEW
            );

            $this->applier->updateEntryScore($entry);
            $this->em->flush();

            if ($complete) {
                $this->addFlash('success', 'Protocole complété et recalculé.');

                return $this->redirectToRoute('app_rider_profile', ['_fragment' => 'tab-competitions']);
            }

            $this->addFlash('warning', 'Notes enregistrées, il en manque encore.');

            return $this->redirectToRoute('app_protocol_show', ['id' => $protocol->getId()]);
        }

        return $this->render('protocol/show.html.twig', [
            'protocol' => $protocol,
            'form' => $form,
        ]);
    }

}