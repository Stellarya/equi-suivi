<?php

namespace App\Controller;

use App\Entity\Rider;
use App\Form\RiderGalopType;
use App\Form\RiderType;
use App\Service\RiderGalopService;
use App\Service\RiderProfileService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rider', name: 'app_rider')]
final class RiderProfileController extends AppController
{
    public function __construct(
        private readonly RiderProfileService $riderProfileService,
        private readonly RiderGalopService $riderGalopService
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile', name: '_profile', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getCurrentAppUser();
        $rider = $user->getRider();

        $profileData = $this->riderProfileService->buildProfileViewData($rider);
        $profileData['isProfileModalOpen'] = false;
        $profileData['isRiderGalopModalOpen'] = false;

        if ($rider !== null) {
            $profileData['form'] = $this->createRiderProfileFormView($rider);
            $profileData['riderGalopForm'] = $this->createRiderGalopFormView($rider);
            $profileData['riderGalopEditForms'] = $this->createRiderGalopEditFormViews($rider);
            $profileData['openRiderGalopEditModalId'] = null;
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
        $profileData['riderGalopForm'] = $this->createRiderGalopFormView($rider);
        $profileData['isProfileModalOpen'] = true;
        $profileData['isRiderGalopModalOpen'] = false;

        return $this->render('rider_profile/index.html.twig', $profileData);
    }

    private function createRiderProfileFormView(Rider $rider): FormView
    {
        return $this->createForm(RiderType::class, $rider, [
            'action' => $this->generateUrl('app_rider_profile_edit'),
            'method' => 'POST',
        ])->createView();
    }

    private function createRiderGalopFormView(Rider $rider): FormView
    {
        $riderGalop = $this->riderGalopService->createForRider($rider);

        return $this->createForm(RiderGalopType::class, $riderGalop, [
            'action' => $this->generateUrl('app_rider_galop_add'),
            'method' => 'POST',
        ])->createView();
    }

    /**
     * @return array<int, FormView>
     */
    private function createRiderGalopEditFormViews(
        Rider $rider,
        ?int $invalidFormRiderGalopId = null,
        ?FormInterface $invalidForm = null
    ): array {
        $forms = [];

        foreach ($this->riderProfileService->getSortedGalopHistory($rider) as $riderGalop) {
            if ($riderGalop->getId() === null) {
                continue;
            }

            if ($invalidFormRiderGalopId === $riderGalop->getId() && $invalidForm !== null) {
                $forms[$riderGalop->getId()] = $invalidForm->createView();

                continue;
            }

            $forms[$riderGalop->getId()] = $this->createForm(RiderGalopType::class, $riderGalop, [
                'action' => $this->generateUrl('app_rider_galop_edit', [
                    'id' => $riderGalop->getId(),
                ]),
                'method' => 'POST',
            ])->createView();
        }

        return $forms;
    }
}