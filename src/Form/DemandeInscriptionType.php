<?php

namespace App\Form;

use App\DataTransfer\DemandeInscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contact', null, ['label' => 'Nom & Prénom', 'required' => true])
            ->add('email', EmailType::class, ['label' => 'Courriel', 'required' => true])
            ->add('mobile', TelType::class, ['label' => 'N° de mobile', 'required' => true])
            ->add('etablissement', null, ['label' => 'Nom', 'required' => true])
            ->add('role', null, ['label' => "Votre rôle au sein de l'établissement", 'required' => true])
            ->add('codePostal', null, ['label' => 'Code postal', 'required' => true])
            ->add('localite', null, ['label' => 'Ville', 'required' => true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DemandeInscription::class,
        ]);
    }
}
