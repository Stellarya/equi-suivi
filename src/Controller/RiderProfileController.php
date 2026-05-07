<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Service\RiderProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RiderProfileController extends AbstractController
{
    public function __construct(
        private readonly RiderProfileService $riderProfileService
    )
    {
    }

    #[Route('/mon-profil', name: 'app_rider_profile')]
    public function index(): Response
    {
        $user = $this->getUser();

        if(!$user instanceof AppUser) {
            throw $this->createAccessDeniedException();
        }

        return $this->render(
            'rider_profile/index.html.twig', 
            $this->riderProfileService->buildProfileViewData($user->getRider()));
    }
}
