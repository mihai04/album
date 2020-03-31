<?php

namespace AlbumBundle\Form;

use AlbumBundle\Entity\Album;
use Doctrine\DBAL\Types\StringType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddAPIAlbumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter the title here.'
                ],
            ])
            ->add('summary', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Tell us about your album.'
                ]
            ])
            ->add('artist', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter the artist here.'
                ]
            ])
            ->add('isrc', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter the ISRC here.'
                ]
            ])
            ->add('albumTracks', CollectionType::class, [
                    'mapped' => false,
                    'required' => true,
                    'entry_type' => TrackEmbeddedForm::class,
                    'allow_add' => true,
                    'by_reference' => false,
                    'allow_delete' => true,
                    'label' => false,
                    'entry_options' => ['label' => false],
                ]
            )
            ->add('published', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'published'
                ]
            ])
            ->add('listeners', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'listeners'
                ]
            ])
            ->add('playcount', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'playcount'
                ]
            ])
            ->add('tags', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'tags'
                ]
            ])
            ->add('image', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'image'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'addAlbum'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Album::class,
            "allow_extra_fields" => true
        ]);
    }

    public function getBlockPrefix()
    {
        return 'album_bundle_add_apialbum_type';
    }
}
