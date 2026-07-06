<?php

namespace App\Form\Type;

use App\Entity\Department;
use App\Entity\Ranch;
use App\Entity\Region;
use App\Repository\DepartmentRepository;
use App\Repository\RanchRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface; // 👈 Important
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationSelectorType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('region', EntityType::class, [
            'class' => Region::class,
            'label' => 'locationSelection.region',
            'choice_label' => 'libelle',
            'placeholder' => 'Sélectionnez une région...',
            'required' => $options['required'],
            'mapped' => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $form = $event->getForm();

            $regionId = !empty($data['region']) ? $data['region'] : null;
            $departmentId = !empty($data['department']) ? $data['department'] : null;

            $form->add('department', EntityType::class, [
                'class' => Department::class,
                'label' => 'locationSelection.department',
                'choice_label' => function (Department $d) {
                    return $d->getNumberDepartment() . ' - ' . $d->getName();
                },
                'placeholder' => 'Sélectionnez un département...',
                'required' => $options['required'],
                'mapped' => false,
                'disabled' => $regionId === null,
                'query_builder' => function (DepartmentRepository $repo) use ($regionId) {
                    if (!$regionId) {
                        return $repo->createQueryBuilder('d')->where('1 = 0');
                    }
                    return $repo->createQueryBuilder('d')
                        ->where('d.region = :regionId')
                        ->setParameter('regionId', $regionId)
                        ->orderBy('d.numberDepartment', 'ASC');
                },
            ]);

            $form->add('ranch', EntityType::class, [
                'class' => Ranch::class,
                'label' => 'locationSelection.ranch',
                'choice_label' => 'name',
                'placeholder' => 'Sélectionnez une écurie...',
                'required' => $options['required'],
                'disabled' => $departmentId === null,
                'query_builder' => function (RanchRepository $repo) use ($departmentId) {
                    if (!$departmentId) {
                        return $repo->createQueryBuilder('r')->where('1 = 0');
                    }
                    return $repo->createQueryBuilder('r')
                        ->where('r.department = :departmentId')
                        ->setParameter('departmentId', $departmentId)
                        ->orderBy('r.name', 'ASC');
                },
            ]);
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $ranch = $event->getData();

            $currentRegion = null;
            $currentDepartment = null;

            if ($ranch instanceof Ranch) {
                $currentDepartment = $ranch->getDepartment();
                $currentRegion = $currentDepartment?->getRegion();
            }

            $form->add('department', EntityType::class, [
                'class' => Department::class,
                'label' => 'locationSelection.department',
                'choice_label' => function (Department $d) {
                    return $d->getNumberDepartment() . ' - ' . $d->getName();
                },
                'placeholder' => $currentRegion ? 'Sélectionnez un département...' : 'Sélectionnez d\'abord une région',
                'disabled' => $currentRegion === null,
                'required' => $options['required'],
                'mapped' => false,
                'data' => $currentDepartment,
                'query_builder' => function (DepartmentRepository $repo) use ($currentRegion) {
                    if (!$currentRegion) {
                        return $repo->createQueryBuilder('d')->where('1 = 0');
                    }
                    return $repo->createQueryBuilder('d')
                        ->where('d.region = :region')
                        ->setParameter('region', $currentRegion)
                        ->orderBy('d.numberDepartment', 'ASC');
                },
            ]);

            $form->add('ranch', EntityType::class, [
                'class' => Ranch::class,
                'label' => 'locationSelection.ranch',
                'choice_label' => 'name',
                'placeholder' => $currentDepartment ? 'Sélectionnez une écurie...' : 'Sélectionnez d\'abord un département',
                'disabled' => $currentDepartment === null,
                'required' => $options['required'],
                'data' => $ranch,
                'query_builder' => function (RanchRepository $repo) use ($currentDepartment) {
                    if (!$currentDepartment) {
                        return $repo->createQueryBuilder('r')->where('1 = 0');
                    }
                    return $repo->createQueryBuilder('r')
                        ->where('r.department = :department')
                        ->setParameter('department', $currentDepartment)
                        ->orderBy('r.name', 'ASC');
                },
            ]);

            if ($currentRegion) {
                $form->get('region')->setData($currentRegion);
            }
        });

        $builder->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ranch::class,
        ]);
    }

    /**
     * Remplit les champs du formulaire lors du chargement initial (Edit Mode)
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        if (null === $viewData) {
            return;
        }

        $forms = iterator_to_array($forms);

        if ($viewData instanceof Ranch) {

            if (isset($forms['ranch'])) {
                $forms['ranch']->setData($viewData);
            }
            
            $department = $viewData->getDepartment();
            if ($department) {
                if (isset($forms['department'])) {
                    $forms['department']->setData($department);
                }
                if (isset($forms['region'])) {
                    $forms['region']->setData($department->getRegion());
                }
            }
        }
    }

    /**
     * Extrait la valeur finale du formulaire pour la donner à l'entité parente (Competition)
     */
    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);
        if (isset($forms['ranch'])) {
            $viewData = $forms['ranch']->getData();
        }
    }
}