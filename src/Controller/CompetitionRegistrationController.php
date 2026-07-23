<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\CompetitionRegistration;
use App\Form\CompetitionRegistrationType;
use App\Form\CompetitionType;
use App\Repository\RanchRepository;
use App\Repository\StatusCompetitionRepository;
use App\Service\CompetitionRegistrationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ECURIE')]
class CompetitionRegistrationController extends AbstractController
{
    #[Route('/competition/{id}/register', name: 'app_competition_register')]
    public function register(
        Competition $competition, 
        Request $request, 
        CompetitionRegistrationService $registrationService,
        RanchRepository $ranchRepository,
        StatusCompetitionRepository $statusCompetitionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        
        $user = $this->getUser();
        $ranch = $ranchRepository->findOneBy(['owner' => $user]);

        if (!$ranch) {
            $this->addFlash('danger', 'Aucune écurie n\'est associée à votre compte.');
            return $this->redirectToRoute('app_competition_index');
        }

        $canEditCompetition = $this->isGranted('ROLE_ADMIN') || ($competition->getLocation() === $ranch);

        $competitionFormView = null;
        if ($canEditCompetition) {
            $competitionForm = $this->createForm(CompetitionType::class, $competition);

            if($request->request->has('competition')) {
                $competitionForm->handleRequest($request);

                if($competitionForm->isSubmitted() && $competitionForm->isValid()) {
                    $entityManager->flush();

                    $this->addFlash('success', 'La compétition a été modifiée avec succès.');
                    
                    return $this->redirectToRoute('app_competition_register', ['id' => $competition->getId()]);
                }
            }
            $competitionFormView = $competitionForm->createView();
        }

        // 2. Création de l'entité et du formulaire
        $registration = new CompetitionRegistration();
        $registrationForm = $this->createForm(CompetitionRegistrationType::class, $registration, [
            'ranch' => $ranch,
        ]);

        if ($request->request->has('competition_registration')) {
            $registrationForm->handleRequest($request);
            if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
                
                $statusPropose = $statusCompetitionRepository->findOneBy(['mnemonique' => 'PROPOSEE']);
                
                if (!$statusPropose) {
                    throw new \Exception("Le statut avec le mnémonique 'PROPOSEE' n'existe pas en base de données. Pensez à vérifier vos fixtures ou inserts.");
                }

                $registration->setStatusRegistration($statusPropose);

                $registrationService->registerCouple($registration, $competition);

                $this->addFlash('success', 'Le couple a été proposé avec succès.');
                return $this->redirectToRoute('app_competition_register', ['id' => $competition->getId()]);
            }
        }

        return $this->render('competition_registration/competition_registration_details.html.twig', [
            'competition' => $competition,
            'form' => $registrationForm->createView(),
            'competitionForm' => $competitionFormView,
            'canEditCompetition' => $canEditCompetition,
            'ranch' => $ranch
        ]);
    }
}