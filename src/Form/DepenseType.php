<?php

namespace App\Form;

use App\Entity\Activite;
use App\Entity\Depense;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $kermesse = $options['kermesse'];
        $builder
            ->add('activite', EntityType::class, [
                'class' => Activite::class,
                'choices' => $kermesse->getActivites(),
                'choice_label' => 'nom'
            ])
            ->add('montant', MoneyType::class, [
                'divisor' => 100
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Depense::class,
        ]);
        $resolver->setRequired([
            'kermesse'
        ]);
    }
}
