<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Entity\Rider;
use App\Entity\RiderGalop;
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

#[Route('/rider/galop', name: 'app_rider_galop')]
final class RiderGalopController extends AppController
{
    public function __construct(
        private readonly RiderProfileService $riderProfileService,
        private readonly RiderGalopService $riderGalopService
    )
    {}

    #[IsGranted('ROLE_USER')]
    #[Route('/add', name: '_add', methods: ['POST'])]
    public function add(Request $request): Response
    {
       $user = $this->getCurrentAppUser();
       $rider = $this->riderProfileService->getRiderForUser($user);

       $riderGalop = $this->riderGalopService->createForRider($rider);

       $form = $this->createForm(RiderGalopType::class, $riderGalop, [
        'action' => $this->generateUrl('app_rider_galop_add'),
        'method' => 'POST'
       ]);

       $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid()) {
        $this->riderGalopService->save($riderGalop);

        $this->addFlash('success', 'Galop ajouté avec succès.');

        return $this->redirectToRoute('app_rider_profile');
       }

       $profileData = $this->riderProfileService->buildProfileViewData($rider);

       $profileData['form'] = $this->createForm(RiderType::class, $rider, [
        'action' => $this->generateUrl('app_rider_profile_edit'),
        'method' => 'POST'
       ])->createView();

       $profileData['riderGalopForm'] = $form->createView();
       $profileData['isProfileModalOpen'] = false;
       $profileData['isRiderGalopModalOpen'] = true;

       return $this->render('rider_profile/index.html.twig', $profileData);
    }

    #[Route('/{id}/edit', name:'_edit', methods: ['POST'])]
    public function edit(RiderGalop $riderGalop, Request $request): Response {
        $user = $this->getCurrentAppUser();
        $rider = $this->riderProfileService->getRiderForUser($user);

        $this->riderGalopService->assertBelongsToRider($riderGalop, $rider);

        $form = $this->createForm(RiderGalopType::class, $riderGalop, [
            'action' => $this->generateUrl('app_rider_galop_edit', [
                'id' => $riderGalop->getId()
            ]),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->riderGalopService->save($riderGalop);
            $this->addFlash('success', 'Galop modifié avec succès.');
            return $this->redirectToRoute('app_rider_profile');
        }

        $profileData = $this->riderProfileService->buildProfileViewData($rider);

        $profileData['form'] = $this->createRiderProfileFormView($rider);
        $profileData['riderGalopForm'] = $this->createRiderGalopFormView($rider);
        $profileData['riderGalopEditForms'] = $this->createRiderGalopEditFormViews($rider, $riderGalop->getId(), $form);
        $profileData['isProfileModalOpen'] = false;
        $profileData['isRiderGalopModalOpen'] = false;
        $profileData['openRiderGalopEditModalId'] = $riderGalop->getId();

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
