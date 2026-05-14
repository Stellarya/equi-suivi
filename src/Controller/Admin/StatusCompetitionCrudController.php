<?php

namespace App\Controller\Admin;

use App\Entity\StatusCompetition;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StatusCompetitionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return StatusCompetition::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('libelle'),
            TextField::new('mnemonique'),
            BooleanField::new('estActif')
        ];
    }

}