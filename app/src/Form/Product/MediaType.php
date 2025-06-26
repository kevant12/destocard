<?php

namespace App\Form\Product;

use App\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Url;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filePath', TextType::class, [
                'label' => 'URL du média (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://... (ou laissez vide si vous uploadez un fichier)'
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le chemin ou l\'URL ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier (image ou vidéo)',
                'mapped' => false,
                'required' => false,
                'multiple' => false,
                'attr' => [
                    'accept' => 'image/*,video/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/*',
                            'video/*'
                        ],
                        'mimeTypesMessage' => 'Merci d\'uploader une image ou une vidéo valide',
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'media_form'
        ]);
    }
} 