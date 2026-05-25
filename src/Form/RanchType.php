<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\Ranch;
use App\Entity\Region;
use App\Repository\DepartmentRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RanchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom de l\'écurie'])
            ->add('address', TextType::class, ['label' => 'Adresse'])
            ->add('phone', IntegerType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('region', EntityType::class, [
                'class' => Region::class,
                'choice_label' => 'libelle',
                'label' => 'Région',
                'mapped' => false,
                'placeholder' => 'Sélectionnez une région...',
                'required' => false,
            ])
        ;

        // Fonction réutilisable pour ajouter le champ Department filtré
        $addDepartmentField = function (FormInterface $form, ?Region $region) {
            $form->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => function (Department $department) {
                    return $department->getNumberDepartment() . ' - ' . $department->getName();
                },
                'label' => 'Département',
                'placeholder' => $region ? 'Sélectionnez un département...' : 'Sélectionnez d\'abord une région',
                'disabled' => $region === null,
                'query_builder' => function (DepartmentRepository $repo) use ($region) {
                    return $repo->createQueryBuilder('d')
                        ->where('d.region = :region')
                        ->setParameter('region', $region)
                        ->orderBy('d.code', 'ASC');
                },
            ]);
        };

        // 1. Au chargement initial du formulaire (Création ou Modification)
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($addDepartmentField) {
            $ranch = $event->getData();
            $form = $event->getForm();

            $department = $ranch?->getDepartment();
            $region = $department?->getRegion();

            if ($region) {
                $form->get('region')->setData($region);
            }

            $addDepartmentField($form, $region);
        });

        // 2. À la soumission (quand l'utilisateur valide ou quand on fait un appel AJAX)
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            $regionId = $data['region'] ?? null;
            
            $form->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez un département...',
                'query_builder' => function (DepartmentRepository $repo) use ($regionId) {
                    return $repo->createQueryBuilder('d')
                        ->where('d.region = :regionId')
                        ->setParameter('regionId', $regionId)
                        ->orderBy('d.code', 'ASC');
                },
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ranch::class,
        ]);
    }
}