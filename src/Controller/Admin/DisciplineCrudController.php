<?php

namespace App\Controller\Admin;

use App\Entity\Discipline;
use App\Repository\TypeEquitationRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DisciplineCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Discipline::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('libelle'),
            TextField::new('mnemonique'),
            AssociationField::new('typeEquitation')
                ->setLabel('Type d\'équitation')
                ->setFormTypeOption('query_builder', static function (TypeEquitationRepository $typeEquitationRepository) {
                    return $typeEquitationRepository->createQueryBuilder('typeEquitation')
                        ->andWhere('typeEquitation.estActif = :active')
                        ->setParameter('active', true)
                        ->orderBy('typeEquitation.libelle', 'ASC');
                }),
            BooleanField::new('estActif')
        ];
    }
}
