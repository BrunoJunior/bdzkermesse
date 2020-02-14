<?php

namespace App\Form;

use App\Entity\Activite;
use App\Entity\Recette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $kermesse = $options['kermesse'];
        if ($kermesse !== null) {
            $builder->add('activite', EntityType::class, [
                'class' => Activite::class,
                'choices' => $kermesse->getActivites(),
                'choice_label' => 'nom',
                'disabled' => $options['activite'] instanceof Activite
            ]);
        }
        $builder->add('report_stock', CheckboxType::class , [
                'required' => false
            ])
            ->add('libelle')
            ->add('date', DatePickerType::class)
            ->add('montant', MoneyType::class, [
                'divisor' => 100
            ])
            ->add('nombre_ticket', NumberType::class, [
                'attr' => ['data-activites_autorisees' => json_encode($options['acceptent_tickets'])]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
            'activite' => null,
            'acceptent_tickets' => [],
            'kermesse' => null,
        ]);
    }
}
