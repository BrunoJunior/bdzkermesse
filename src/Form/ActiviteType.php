<?php

namespace App\Form;

use App\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nom');
        if ($options['tickets']) {
            $builder
                ->add('accepteTickets', CheckboxType::class, [
                    'label'    => 'Accepte les tickets ?',
                    'required' => false
                ])
                ->add('accepteMonnaie', CheckboxType::class, [
                    'label'    => 'Accepte la monnaie ?',
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
