<?php

namespace App\Form;

use App\Entity\Pension;
use App\Entity\Ranch;
use App\Entity\TypePension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PensionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ranch', EntityType::class, [
                'class' => Ranch::class,
                'choice_label' => 'name',
                'label' => 'pension.ranch',
                'placeholder' => 'Choisir un ranch',
                'required' => false,
            ])
            ->add('typePension', EntityType::class, [
                'class' => TypePension::class,
                'choice_label' => 'libelle',
                'label' => 'pension.type',
                'placeholder' => 'Choisir un type de pension',
                'required' => false,
            ])
            ->add('entryDate', DateType::class, [
                'label' => 'pension.entry_date',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'label' => 'pension.end_date',
                'widget' => 'single_text',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pension::class,
        ]);
    }
}