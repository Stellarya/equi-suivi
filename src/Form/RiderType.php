<?php

namespace App\Form;

use App\Entity\Rider;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'rider.firstName',
                'required' => true
            ])
            ->add('lastName', TextType::class, [
                'label' => 'rider.lastName',
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rider::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'rider'
        ]);
    }
}
