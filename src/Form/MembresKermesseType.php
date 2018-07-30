<?php

namespace App\Form;

use App\Entity\Kermesse;
use App\Entity\Membre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembresKermesseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $kermesse = $builder->getData();
        $builder
            ->add('membres', EntityType::class, [
                'class' => Membre::class,
                'choices' => $kermesse->getEtablissement()->getMembres(),
                'multiple' => true,
                'expanded' => true,
                'mapped' => true,
                'choice_label' => function (Membre $membre) {
                    return $membre->getPrenom() . ' ' . $membre->getNom();
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Kermesse::class,
        ]);
    }
}
