<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Form\RiderType;
use App\Service\RiderProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rider', name: 'app_rider')]
final class RiderProfileController extends AbstractController
{
    public function __construct(
        private readonly RiderProfileService $riderProfileService,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile', name: '_profile', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getCurrentAppUser();
        $rider = $user->getRider();

        $profileData = $this->riderProfileService->buildProfileViewData($rider);

        if ($rider !== null) {
            $profileData['form'] = $this->createForm(RiderType::class, $rider, [
                'action' => $this->generateUrl('app_rider_profile_edit'),
                'method' => 'POST',
            ])->createView();
        }

        return $this->render('rider_profile/index.html.twig', $profileData);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/edit', name: '_profile_edit', methods: ['POST'])]
    public function edit(Request $request): Response
    {
        $user = $this->getCurrentAppUser();
        $rider = $this->riderProfileService->getRiderForUser($user);

        $form = $this->createForm(RiderType::class, $rider, [
            'action' => $this->generateUrl('app_rider_profile_edit'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->riderProfileService->saveProfile();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('app_rider_profile');
        }

        $profileData = $this->riderProfileService->buildProfileViewData($rider);
        $profileData['form'] = $form->createView();
        $profileData['isProfileModalOpen'] = true;

        return $this->render('rider_profile/index.html.twig', $profileData);
    }

    private function getCurrentAppUser(): AppUser
    {
        $user = $this->getUser();

        if (!$user instanceof AppUser) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}