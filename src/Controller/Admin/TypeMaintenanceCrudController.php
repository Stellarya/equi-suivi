<?php

namespace App\Controller\Admin;

use App\Entity\TypeMaintenance;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TypeMaintenanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TypeMaintenance::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
           IdField::new('id')->hideOnForm(),

            TextField::new('libelle')
                ->setLabel('Libellé'),

            TextField::new('mnemonique')
                ->setLabel('Mnémonique'),

            IntegerField::new('intervalDefaultValue')
                ->setLabel('Fréquence')
                ->setHelp('Exemples : 1, 3, 6.'),

            ChoiceField::new('intervalDefaultUnit')
                ->setLabel('Unité')
                ->setChoices(TypeMaintenance::INTERVAL_UNIT_CHOICES),

            TextField::new('intervalDefaultLabel')
                ->setLabel('Intervalle par défaut')
                ->onlyOnIndex(),

            TextareaField::new('conseils')
                ->setLabel('Conseils')
                ->hideOnIndex(),

            BooleanField::new('estActif')
                ->setLabel('Actif'),
        ];
    }

}
