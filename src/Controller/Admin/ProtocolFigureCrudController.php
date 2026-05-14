<?php

namespace App\Controller\Admin;

use App\Entity\ProtocolFigure;
use App\Repository\DressageTestRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class ProtocolFigureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProtocolFigure::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('dressageTest')
                ->setLabel('Reprise')
                ->setFormTypeOption('query_builder', static function (DressageTestRepository $dressageTestRepository) {
                    return $dressageTestRepository->createQueryBuilder('dressageTest')
                        ->andWhere('dressageTest.estActif = :active')
                        ->setParameter('active', true)
                        ->orderBy('dressageTest.libelle', 'ASC');
                }),
            ChoiceField::new('section')
                ->setLabel('Section')
                ->setChoices(ProtocolFigure::SECTION_CHOICES),
            
            NumberField::new('ordre')
                ->setLabel('Ordre')
                ->setHelp('Ordre réel d\'affichage dans le protocole.'),

            NumberField::new('number')
                ->setLabel('N° figure')
                ->setHelp('Numéro visible sur le protocole. Peut être vide pour certaines lignes.'),

            TextField::new('label')
                ->setLabel('Libellé')
                ->setHelp('Exemples : Allures, Impulsion, Le pas, Chorégraphie.'),
            
            TextareaField::new('directiveIdeas')
                ->setLabel('Idées directrices')
                ->setHelp('Critères attendus par le juge pour cette figure.'),

            NumberField::new('maxPoints')
                ->setLabel('Points max')
                ->setHelp('Souvent 10.'),

            NumberField::new('coefficient')
                ->setLabel('Coefficient')
                ->setNumDecimals(2)
                ->setHelp('Exemple : 1, 2, 0.5'),

            BooleanField::new('estActif')
                ->setLabel('Actif')
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('dressageTest', 'Reprise'))
            ->add(BooleanFilter::new('estActif', 'Actif'));
    }

}
