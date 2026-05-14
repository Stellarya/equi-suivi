<?php

namespace App\Controller\Admin;

use App\Entity\DressageTest;
use App\Repository\CategoryRepository;
use App\Repository\DisciplineRepository;
use App\Repository\LevelRepository;
use App\Repository\TypeTestRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DressageTestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DressageTest::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('libelle'),
            TextField::new('mnemonique'),
            AssociationField::new('discipline')
                ->setLabel('Discipline')
                ->setFormTypeOption('query_builder', static function (DisciplineRepository $disciplineRepository) {
                    return $disciplineRepository->createQueryBuilder('discipline')
                        ->andWhere('discipline.estActif = :active')
                        ->setParameter('active', true)
                        ->orderBy('discipline.libelle', 'ASC');
                }),
            AssociationField::new('category')
                ->setLabel('Catégorie')
                ->setFormTypeOption('query_builder', static function (CategoryRepository $categoryRepository) {
                    return $categoryRepository->createQueryBuilder('category')
                        ->andWhere('category.estActif = :active')
                        ->setParameter('active', true)
                        ->orderBy('category.libelle', 'ASC');
                }),
            AssociationField::new('level')
                ->setLabel('Niveau')
                ->setFormTypeOption('query_builder', static function (LevelRepository $levelRepository) {
                    return $levelRepository->createQueryBuilder('level')
                        ->andWhere('level.estActif = :active')
                        ->setParameter('active', true)
                        ->orderBy('level.libelle', 'ASC');
                }),
            AssociationField::new('typeTest')
                ->setLabel('Type Reprise')
                ->setFormTypeOption('query_builder', static function (TypeTestRepository $typeTestRepository) {
                    return $typeTestRepository->createQueryBuilder('typeTest')
                        ->andWhere('typeTest.estActif = :active')
                        ->setParameter('active', true)
                        ->orderBy('typeTest.libelle', 'ASC');
                }),
            BooleanField::new('estActif')
        ];
    }

}
