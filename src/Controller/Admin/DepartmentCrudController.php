<?php

namespace App\Controller\Admin;

use App\Entity\Department;
use App\Repository\RegionRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DepartmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Department::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            IntegerField::new('numberDepartment'),
            TextField::new('name'),
            AssociationField::new('region')
                ->setLabel('Region')
                ->setFormTypeOption('query_builder', static function (RegionRepository $regionRepository) {
                    return $regionRepository->createQueryBuilder('region')
                        ->andWhere('region.estActif = :active')
                        ->setParameter('active', true)
                        ->orderBy('region.libelle', 'ASC');
                }),
            TextField::new('mnemonique'),
            BooleanField::new('estActif')
        ];
    }

}
