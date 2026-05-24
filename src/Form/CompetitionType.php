<?php

namespace App\Form;

use App\Entity\Competition;
use App\Entity\StatusCompetition;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'competition.name',
                'attr' => ['placeholder' => 'ex: Grand National - Saumur']
            ])
            ->add('startDate', DateType::class, [
                'label' => 'competition.startDate',
                'widget' => 'single_text'
            ])
            ->add('endDate', DateType::class, [
                'label' => 'competition.endDate',
                'widget' => 'single_text'
            ])
            ->add('location', TextType::class, [
                'label' => 'competition.location',
                'attr' => ['placeholder' => 'ex: Pôle Hippique du Grand Format']
            ])
            ->add('statusCompetition', EntityType::class, [
                'class' => StatusCompetition::class,
                'choice_label' => 'libelle',
                'label' => 'competition.statusCompetition'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competition::class,
        ]);
    }
}
