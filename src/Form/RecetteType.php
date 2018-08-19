<?php

namespace App\Form;

use App\Entity\Activite;
use App\Entity\Recette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $kermesse = $options['kermesse'];
        if (!$options['activite'] instanceof Activite) {
            $builder->add('activite', EntityType::class, [
                    'class' => Activite::class,
                    'choices' => $kermesse->getActivites(),
                    'choice_label' => 'nom'
                ]);
        }
        $builder
            ->add('report_stock', CheckboxType::class)
            ->add('libelle')
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('montant', MoneyType::class, [
                'divisor' => 100
            ])
            ->add('nombre_ticket', NumberType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
            'activite' => null,
        ]);
        $resolver->setRequired([
            'kermesse'
        ]);
    }
}
