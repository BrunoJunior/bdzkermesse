<?php

namespace App\Form;

use App\Entity\Kermesse;
use App\Entity\Membre;
use App\Entity\Ticket;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $kermesse = $options['kermesse'];
        $builder
            ->add('date', DateType::class)
            ->add('fournisseur', TextType::class)
            ->add('numero', TextType::class)
            ->add('montant', MoneyType::class, ['divisor' => 100])
            ->add('membre', EntityType::class, [
                'class' => Membre::class,
                'choices' => $kermesse->getMembres(),
                'choice_label' => function (Membre $membre) {
                return $membre->getPrenom() . ' ' . $membre->getNom();
            }])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
        $resolver->setRequired([
            'kermesse'
        ]);
    }
}
