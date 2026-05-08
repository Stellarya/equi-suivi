<?php

namespace App\Form;

use App\Entity\Galop;
use App\Entity\RiderGalop;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiderGalopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('galop', EntityType::class, [
                'class' => Galop::class,
                'choice_label' => 'libelle',
                'label' => 'rider.galop',
                'placeholder' => 'rider.choose_galop',
                'required' => true,
            ])
            ->add('obtainedYear', IntegerType::class, [
                'label' => 'rider.obtentionYear',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RiderGalop::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'rider_galop'
        ]);
    }
}
