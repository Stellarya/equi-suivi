<?php

namespace App\Controller\Admin;

use App\Entity\Level;
use App\Repository\CategoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LevelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Level::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('libelle'),
            TextField::new('mnemonique'),
            AssociationField::new('category')
                ->setLabel('Catégorie')
                ->setFormTypeOption('query_builder', static function (CategoryRepository $categoryRepository) {
                    return $categoryRepository->createQueryBuilder('category')
                        ->andWhere('category.estActif = :active')
                        ->setParameter('active', true)
                        ->orderBy('category.libelle', 'ASC');
                }),
            BooleanField::new('estActif')
        ];
    }

}
