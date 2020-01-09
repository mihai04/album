<?php

namespace AlbumBundle\Form;

use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\Track;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrackEmbeddedForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('track_name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter the track name here.'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Track::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'album_bundle_track_embedded_form';
    }
}
