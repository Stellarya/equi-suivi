<?php

namespace App\Controller\Admin;

use App\Entity\ProtocolFigure;
use App\Entity\ProtocolMovement;
use App\Repository\ProtocolFigureRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class ProtocolMovementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProtocolMovement::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('protocolFigure')
                ->setLabel('Figure')
                ->setFormTypeOption('query_builder', static function (ProtocolFigureRepository $protocolFigureRepository) {
                    return $protocolFigureRepository->createQueryBuilder('protocolFigure')
                        ->innerJoin('protocolFigure.dressageTest', 'dressageTest')
                        ->andWhere('protocolFigure.estActif = :active')
                        ->andWhere('protocolFigure.section = :technical')
                        ->setParameter('active', true)
                        ->setParameter('technical', ProtocolFigure::SECTION_TECHNICAL)
                        ->orderBy('dressageTest.libelle', 'ASC')
                        ->addOrderBy('protocolFigure.ordre', 'ASC');
                }),

            NumberField::new('ordre')
                ->setLabel('Ordre'),

            TextField::new('marker')
                ->setLabel('Repère')
                ->setHelp('Exemples : A, X, XC, Entre K et A.'),

            TextEditorField::new('description')
                ->setLabel('Description'),

            BooleanField::new('estActif')
                ->setLabel('Actif'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('protocolFigure', 'Figure'))
            ->add(BooleanFilter::new('estActif', 'Actif'));
    }
}
