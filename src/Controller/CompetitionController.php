<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Form\CompetitionType;
use App\Service\CompetitionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/competition', name: 'app_competition_')]
final class CompetitionController extends AppController
{
    public function __construct(
        private readonly CompetitionService $competitionService,
    ) {
    }

    /**
     * List of all comeptitions
     */
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(): Response
    {
        $competitions = $this->competitionService->getAllCompetitions();
        
        $competitionEditForms = [];
        $openCompetitionEditModalId = null;

        if ($this->isGranted('ROLE_ADMIN')) {
            foreach ($competitions as $competition) {
                $form = $this->createForm(CompetitionType::class, $competition, [
                    'action' => $this->generateUrl('app_competition_edit', ['id' => $competition->getId()]),
                ]);
                
                $competitionEditForms[$competition->getId()] = $form->createView();
            }
        }

        $newCompetitionFormView = null;
        if($this->isGranted('ROLE_ADMIN')) {
            $newCompetition = new Competition();
            $newForm = $this->createForm(CompetitionType::class, $newCompetition, [
                'action' => $this->generateUrl('app_competition_new')
            ]);
            $newCompetitionFormView = $newForm->createView();
        }

        return $this->render('competition/list.html.twig', [
            'competitions' => $competitions,
            'competitionEditForms' => $competitionEditForms,
            'newCompetitionForm' => $newCompetitionFormView,
            'openCompetitionEditModalId' => $openCompetitionEditModalId
        ]);
    }

    #[Route('/new', name:'new', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        $competition = new Competition();

        $form = $this->createForm(CompetitionType::class, $competition);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->competitionService->saveCompetition($competition);
            $this->addFlash('success', sprintf('La compétition "%s" a bien été créée.', $competition->getName()));            
        } else {
            $this->addFlash('danger', 'Erreur lors de la création de la compétition.');
        }

        return $this->redirectToRoute('app_competition_index');
    }

    public function edit(Request $request, Competition $competition): Response
    {
        $form = $this->createForm(CompetitionType::class, $competition);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->competitionService->saveCompetition($competition);
            $this->addFlash('success', sprintf('La compétition "%s" a bien été modifiée.', $competition->getName()));
        } else {
            $this->addFlash('danger', 'Erreur lors de la modification.');
        }

        return $this->redirectToRoute('app_competition_index');
    }
}
