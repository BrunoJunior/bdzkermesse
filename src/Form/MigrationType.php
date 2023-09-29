<?php

namespace App\Form;

use App\DataTransfer\Migration;
use App\Entity\InscriptionBenevole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MigrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('benevoles', EntityType::class, [
                'class' => InscriptionBenevole::class,
                'choices' => $options['transferables'],
                'label' => 'Veuillez sélectionner les bénévoles concernés',
                'multiple' => true,
                'expanded' => true,
                'mapped' => true,
                'choice_label' => function (InscriptionBenevole $inscriptionBenevole) {
                    $benevole = $inscriptionBenevole->getBenevole();
                    return "{$benevole->getIdentite()} <{$benevole->getEmail()}>";
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Migration::class,
        ]);
        $resolver->setRequired([
            'transferables'
        ]);
    }
}
