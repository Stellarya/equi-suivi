<?php

namespace App\Controller;

use App\Entity\AppUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AppController extends AbstractController {

    protected function getCurrentAppUser(): AppUser {
        $user = $this->getUser();

        if(!$user instanceof AppUser) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}