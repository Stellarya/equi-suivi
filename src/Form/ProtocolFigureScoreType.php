<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ProtocolFigureScore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DivisibleBy;
use Symfony\Component\Validator\Constraints\Range;

final class ProtocolFigureScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('score', NumberType::class, [
            'label' => false,
            'required' => false,
            'scale' => 1,
            'constraints' => [
                new Range(min: 0, max: 10, notInRangeMessage: 'La note doit être comprise entre {{ min }} et {{ max }}.'),
                new DivisibleBy(value: 0.5, message: 'La note doit être un multiple de 0,5.'),
            ],
            'attr' => ['class' => 'protocol-score-input'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ProtocolFigureScore::class]);
    }
}