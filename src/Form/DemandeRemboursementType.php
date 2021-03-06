<?php

namespace App\Form;

use App\Entity\Kermesse;
use App\Entity\Membre;
use App\Entity\Remboursement;
use App\Entity\Ticket;
use App\Helper\HFloat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeRemboursementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('membre', EntityType::class, [
                'class' => Membre::class,
                'disabled' => true,
                'choice_label' => function (Membre $membre) {
                    return $membre->getPrenom() . ' ' . $membre->getNom();
                }
            ])
            ->add('numero_suivi', TextType::class)
            ->add('montant', MoneyType::class, [
                'divisor' => 100,
                'disabled' => true,
            ])
            ->add('tickets', EntityType::class, [
                'class' => Ticket::class,
                'choices' => $options['tickets'],
                'multiple' => true,
                'expanded' => true,
                'mapped' => true,
                'choice_label' => function (Ticket $ticket) {
                    return "Ticket #" . $ticket->getId() . ' du ' . $ticket->getDate()->format('d/m/Y') . '(' . HFloat::getInstance($ticket->getMontant() / 100.0)->getMontantFormatFrancais() . ')';
                },
                // Montant en entier dans les data pour calcul JS
                'choice_attr' => function (Ticket $ticket) {
                    return ['data-montant' => $ticket->getMontant()];
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Remboursement::class,
        ]);
        $resolver->setRequired(['tickets']);
    }
}
