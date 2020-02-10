<?php

namespace App\Form;

use App\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom')
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('creneaux', CollectionType::class, [
                'label' => 'Créneaux horaire',
                'by_reference' => false,
                'entry_type' => CreneauType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ]);
        if ($options['tickets']) {
            $builder
                ->add('accepteTickets', CheckboxType::class, [
                    'label'    => 'Accepte les tickets ?',
                    'required' => false
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
            'tickets' => true,
        ]);
    }
}
