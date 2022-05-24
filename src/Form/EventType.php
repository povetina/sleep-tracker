<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Sleep' => 'sleep',
                    'Food' => 'food'
                ]
            ])
            ->add('started', DateTimeType::class, [
                'html5' => false,
                'time_widget' => 'text',
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'js-datepicker'],
                'date_widget' => 'single_text',
                'format' => 'd-m-y'
            ])
            ->add('finished', DateTimeType::class, [
                'html5' => false,
                'time_widget' => 'text',
                'date_widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'by_reference' => false,
                'expanded' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
