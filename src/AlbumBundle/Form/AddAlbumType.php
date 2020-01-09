<?php

namespace AlbumBundle\Form;

use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\Track;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddAlbumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter the title here.'
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
            ->add('trackList', CollectionType::class, [
                'entry_type' => TrackEmbeddedForm::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
//            ->add('trackList', TextareaType::class, [
//                'attr' => [
//                    'placeholder' => 'Enter the list of tracks here.'
//                ]
//            ])
            ->add('image', FileType::class, [
                'label' => 'Image Upload',
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ]);

        $builder->get('trackList')->addModelTransformer(new CallbackTransformer(
                function ($tagsAsArray) {
                },
                function ($tagsAsString) {
                    return explode(', ', $tagsAsString);
                }
        ));
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
