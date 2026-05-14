<?php

namespace App\Controller\Admin;

use App\Entity\TypeSaddle;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TypeSaddleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeSaddle::class;
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
