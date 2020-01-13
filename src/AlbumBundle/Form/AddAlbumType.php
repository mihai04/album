<?php

namespace AlbumBundle\Form;

use AlbumBundle\Entity\Album;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AlbumBundle\Form\TrackEmbeddedForm;

class AddAlbumType extends AbstractType
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
                    'required' => true,
                    'entry_type' => TrackEmbeddedForm::class,
                    'allow_add' => true,
                    'by_reference' => false,
                    'allow_delete' => true,
                    'label' => false,
                    'entry_options' => ['label' => false],
                ]
            )
            ->add('image', FileType::class, [
                'data_class' => null,
                'required' => true
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
        ]);
    }

    public function getBlockPrefix()
    {
        return 'album_bundle_add_album_type';
    }
}
