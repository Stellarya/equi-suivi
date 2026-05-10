<?php

namespace App\Form;

use App\Entity\Breed;
use App\Entity\Coat;
use App\Entity\Horse;
use App\Repository\BreedRepository;
use App\Repository\CoatRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class HorseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'horse.name',
                'required' => true
            ])
            ->add('affix', TextType::class, [
                'label' => 'horse.affix',
                'required' => false
            ])
            ->add('birthDate', BirthdayType::class, [
                'label' => 'horse.birth_date',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('sire', TextType::class, [
                'label' => 'horse.sire',
                'required' => false,
            ])
            ->add('breed', EntityType::class, [
                'class' => Breed::class,
                'label' => 'horse.breed',
                'choice_label' => 'libelle',
                'placeholder' => 'horse.unknown_breed',
                'required' => true,
                'query_builder' => static function (BreedRepository $breedRepository) {
                    return $breedRepository->createQueryBuilder('breed')
                        ->orderBy('breed.libelle', 'ASC');
                }
            ])
            ->add('coat', EntityType::class, [
                'class' => Coat::class,
                'label' => 'horse.coat',
                'choice_label' => 'libelle',
                'placeholder' => 'horse.unknown_coat',
                'required' => true,
                'query_builder' => static function (CoatRepository $coatRepository) {
                    return $coatRepository->createQueryBuilder('coat')
                        ->orderBy('coat.libelle', 'ASC');
                }
            ])
            ->add('photo', FileType::class, [
                'label' => 'horse.photo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Veuillez importer une image valide : JPG, PNG ou WEBP.'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Horse::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'horse',
        ]);
    }
}
