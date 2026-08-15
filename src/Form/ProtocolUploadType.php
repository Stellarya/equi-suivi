<?php

namespace App\Form;

use App\Entity\Protocol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class ProtocolUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'protocol.file',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotNull(message: 'Veuillez sélectionner un fichier.'),
                    new File(
                        maxSize: '10M',
                        mimeTypes: [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        mimeTypesMessage: 'Formats acceptés : PDF, JPG ou PNG.',
                    ),
                ],
            ])
            ->add('judgePosition', ChoiceType::class, [
                'label' => 'protocol.judge_position',
                'mapped' => false,
                'placeholder' => 'Sélectionner la position du juge',
                'choices' => Protocol::JUDGE_POSITION_CHOICES,
                'constraints' => [new NotNull(message: 'Indiquez la position du juge')]
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Protocol::class,
        ]);
    }

}