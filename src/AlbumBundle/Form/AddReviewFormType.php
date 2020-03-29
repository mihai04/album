<?php

namespace AlbumBundle\Form;

use blackknight467\StarRatingBundle\Form\RatingType;
use AlbumBundle\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('review', TextareaType::class)
            ->add('title', TextType::class, [
                'required' => true
            ])
            ->add('rating', RatingType::class, [
                'label' => 'Rating',
                'required' => false,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'review_bundle_review_form_type';
    }
}
