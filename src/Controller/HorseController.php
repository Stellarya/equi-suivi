<?php

namespace App\Controller;

use App\Entity\Horse;
use App\Form\HorseType;
use App\Service\HorseService;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/horses', name: 'app_horse_')]
final class HorseController extends AppController
{
    public function __construct(
        private readonly HorseService $horseService,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getCurrentAppUser();
        $horse = $this->horseService->createForUser($user);

        return $this->render('horse/list.html.twig', [
            'horses' => $this->horseService->getVisibleHorsesForUser($user),
            'horseForm' => $this->createHorseFormView($horse, 'app_horse_new'),
            'isHorseModalOpen' => false
        ]);
    }

    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $user = $this->getCurrentAppUser();
        $horse = $this->horseService->createForUser($user);

        $form = $this->createForm(HorseType::class, $horse, [
            'action' => $this->generateUrl('app_horse_new'),
            'method' => 'POST'
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $this->horseService->updatePhoto($horse, $form->get('photo')->getData());
            $this->horseService->save($horse);

            $this->addFlash('success', 'Cheval créé avec succès.');

            return $this->redirectToRoute('app_horse_show', [
                'id' => $horse->getId()
            ]);
        }

        return $this->render('horse/list.html.twig', [
            'horses' => $this->horseService->getVisibleHorsesForUser($user),
            'horseForm' => $form->createView(),
            'isHorseModalOpen' => true,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Horse $horse): Response
    {
        $user = $this->getCurrentAppUser();

        $this->horseService->assertCanManageHorse($horse, $user);

        return $this->render('horse/horse-details.html.twig', [
            'horse' => $horse,
            'horseForm' => $this->createHorseFormView($horse, 'app_horse_edit', [
                'id' => $horse->getId(),
            ]),
            'isHorseModalOpen' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['POST'])]
    public function edit(Request $request, Horse $horse): Response
    {
        $user = $this->getCurrentAppUser();

        $this->horseService->assertCanManageHorse($horse, $user);

        $form = $this->createForm(HorseType::class, $horse, [
            'action' => $this->generateUrl('app_horse_edit', [
                'id' => $horse->getId(),
            ]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->horseService->updatePhoto($horse, $form->get('photo')->getData());
            $this->horseService->save($horse);

            $this->addFlash('success', 'Cheval modifié avec succès.');

            return $this->redirectToRoute('app_horse_show', [
                'id' => $horse->getId(),
            ]);
        }

        return $this->render('horse/horse-details.html.twig', [
            'horse' => $horse,
            'horseForm' => $form->createView(),
            'isHorseModalOpen' => true,
        ]);
    }

    #[Route('/{id}/inactivate', name: 'inactivate', methods: ['POST'])]
    public function inactivate(Request $request, Horse $horse): Response
    {
        $user = $this->getCurrentAppUser();

        $this->horseService->assertCanManageHorse($horse, $user);

        if (!$this->isCsrfTokenValid('inactivate_horse_' . $horse->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $status = $request->request->get('status');

        if ($status === Horse::STATUS_RESTING) {
            $this->horseService->setResting($horse);
        } elseif ($status === Horse::STATUS_RETIRED) {
            $this->horseService->retire($horse);
        } else {
            throw $this->createNotFoundException('Statut demandé invalide.');
        }

        $this->addFlash('success', 'Statut du cheval mis à jour.');

        return $this->redirectToRoute('app_horse_show', [
            'id' => $horse->getId(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Horse $horse): Response
    {
        $user = $this->getCurrentAppUser();

        $this->horseService->assertCanManageHorse($horse, $user);

        if (!$this->isCsrfTokenValid('delete_horse_' . $horse->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $this->horseService->delete($horse);

        $this->addFlash('success', 'Cheval supprimé avec succès.');

        return $this->redirectToRoute('app_horse_index');
    }

    private function createHorseFormView(
        Horse $horse,
        string $routeName,
        array $routeParameters = []
    ): FormView {
        return $this->createForm(HorseType::class, $horse, [
            'action' => $this->generateUrl($routeName, $routeParameters),
            'method' => 'POST'
        ])->createView();
    }
}