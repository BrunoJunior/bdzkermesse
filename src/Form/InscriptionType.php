<?php

namespace App\Form;

use App\DataTransfer\Inscription;
use App\Entity\Creneau;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('creneau', EntityType::class, [
                'class' => Creneau::class,
                'label' => 'Choisissez votre créneau',
                'choices' => $options['activite']->getCreneaux()->filter(function (Creneau $creneau) {
                    return $creneau->getInscriptionBenevoles()->count() < $creneau->getNbBenevolesRecquis();
                }),
                'choice_label' => function (Creneau $creneau) {
                    return "De " . $creneau->getDebut()->format('H:i') .
                        " à " . $creneau->getFin()->format('H:i') .
                        " (" . $creneau->getInscriptionBenevoles()->count() . " / " . $creneau->getNbBenevolesRecquis() . " bénévoles)"
                        ;
                },
                'expanded' => true,
                'multiple' => false,
                'mapped' => true,
                'required' => true
            ])
            ->add('nom', null, ['label' => 'Votre nom'])
            ->add('email', EmailType::class, [
                'label' => 'Votre adresse email',
                'required' => true,
                'attr' => ['data-onchange' => $options['findAjax']]
            ])
            ->add('portable', TelType::class, ['label' => 'Votre n° de portable'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
        ]);
        $resolver->setRequired(['activite', 'findAjax']);
    }
}
