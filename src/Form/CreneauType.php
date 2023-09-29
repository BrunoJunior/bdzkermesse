<?php

namespace App\Form;

use App\Entity\Creneau;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreneauType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nbBenevolesRecquis', IntegerType::class, [
                'label' => 'Nombre de bénévoles requis',
                'row_attr' => ['class' => 'creneau-nb-benevoles-requis'],
            ])
            ->add('debut', TimeType::class, [
                'label' => 'De',
                'input' => 'datetime',
                'widget' => 'choice',
                'row_attr' => ['class' => 'creneau_heure'],
            ])
            ->add('fin', TimeType::class, [
                'label' => 'à',
                'input' => 'datetime',
                'widget' => 'choice',
                'row_attr' => ['class' => 'creneau_heure'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Creneau::class,
            'attr' => ['class' => 'activite-creneau']
        ]);
    }
}
