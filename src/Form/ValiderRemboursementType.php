<?php

namespace App\Form;

use App\Entity\Remboursement;
use App\Enum\RemboursementModeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('numero_suivi', TextType::class, [
                'disabled' => true,
            ])
            ->add('montant', MoneyType::class, [
                'divisor' => 100,
                'disabled' => true,
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
