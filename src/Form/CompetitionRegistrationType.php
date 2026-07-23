<?php

namespace App\Form;

use App\Entity\CompetitionRegistration;
use App\Entity\Horse;
use App\Entity\Rider;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionRegistrationType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $ranch = $options['ranch'];

        $builder
            ->add('rider', EntityType::class, [
                'class' => Rider::class,
                'query_builder' => function (EntityRepository $er) use ($ranch) {
                    return $er->createQueryBuilder('rider')
                    ->innerJoin('rider.ranch', 'ranch')
                    ->andWhere('ranch = :myRanch')
                    ->setParameter('myRanch', $ranch)
                    ->orderBy('rider.lastName', 'ASC');
                },
                'choice_label' => function (Rider $rider) {
                    return $rider->getFirstName(). ' ' . $rider->getLastName();
                },
                'label' => 'competitionRegistration.rider',
                'placeholder' => 'Choisir un cavalier'
            ])
            ->add('horse', EntityType::class, [
                'class' => Horse::class,
                'query_builder' => function (EntityRepository $er) use ($ranch) {
                    return $er->createQueryBuilder('horse')
                    ->andWhere('horse.ranch = :myRanch')
                    ->setParameter('myRanch', $ranch)
                    ->orderBy('horse.name', 'ASC');
                },
                'choice_label' => function (Horse $horse) {
                    return $horse->getName() . ' ' . $horse->getAffix();
                },
                'label' => 'competitionRegistration.horse',
                'placeholder' => 'Choisir un cheval'
            ])
            ->add('note', null, [
                'label' => 'competitionRegistration.note',
                'required' => false
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
       $resolver->setDefaults([
        'data_class' => CompetitionRegistration::class,
        'ranch' => null
       ]);
    }
}