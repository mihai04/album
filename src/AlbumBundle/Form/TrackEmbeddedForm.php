<?php

namespace AlbumBundle\Form;

use AlbumBundle\Entity\Track;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TrackEmbeddedForm
 * @package TrackBundle\Form
 */
class TrackEmbeddedForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('trackName', null, [
            'label' => false
        ])
        ->add('duration', HiddenType::class, [
            'required' => false,
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
