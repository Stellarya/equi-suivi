<?php

namespace App\Controller\Admin;

use App\Entity\TypeEquitation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TypeEquitationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeEquitation::class;
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
