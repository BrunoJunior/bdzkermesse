<?php

namespace App\Form;

use App\Entity\Kermesse;
use App\Entity\Membre;
use App\Entity\Ticket;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $kermesse = $options['kermesse'];
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('fournisseur', TextType::class)
            ->add('numero', TextType::class)
            ->add('montant', MoneyType::class, ['divisor' => 100])
            ->add('membre', EntityType::class, [
                'class' => Membre::class,
                'choices' => $kermesse->getMembres(),
                'choice_label' => function (Membre $membre) {
                return $membre->getPrenom() . ' ' . $membre->getNom();
            }])
            ->add('depenses', CollectionType::class, [
                'label' => 'Activités associées',
                'by_reference' => false,
                'entry_type' => DepenseType::class,
                'entry_options' => ['kermesse' => $kermesse],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true
            ])
            ->add('duplicata', FileType::class, [
                'required' => false,
                //BUG #19 - On limite à 1Mo, c'est déjà pas mal pour un ticket ou une facture
                'constraints' => [
                    new File([
                        'maxSize' => '1M',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximum autorisée est de {{ limit }} {{ suffix }}'
                    ])
                ]
            ])
            ->add('commentaire', TextareaType::class, ['required' => false])
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
