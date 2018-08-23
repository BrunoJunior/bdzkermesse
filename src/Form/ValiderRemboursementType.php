<?php

namespace App\Form;

use App\Entity\Membre;
use App\Entity\Remboursement;
use App\Enum\RemboursementModeEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValiderRemboursementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $modes = RemboursementModeEnum::getLabels();
        $builder
            ->add('membre', EntityType::class, [
                'class' => Membre::class,
                'disabled' => true,
                'choice_label' => function (Membre $membre) {
                    return $membre->getPrenom() . ' ' . $membre->getNom();
                }
            ])
            ->add('numero_suivi', TextType::class, [
                'disabled' => true,
            ])
            ->add('montant', MoneyType::class, [
                'divisor' => 100,
                'disabled' => true,
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('mode', ChoiceType::class, [
                'choices' => array_flip($modes),
                'preferred_choices' => [RemboursementModeEnum::VIREMENT]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Remboursement::class,
        ]);
    }
}
