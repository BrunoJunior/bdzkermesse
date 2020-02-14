<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type date avec options par défaut différentes
 * Pour utiliser le datepicker au format fr
 * @package App\Form
 */
class DatePickerType extends DateType
{
    /**
     * widget => single_text
     * html5 => false
     * format => dd/MM/yyyy
     * attr => class => js-datepicker
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'widget' => 'single_text',
            'html5' => false,
            'format' => 'dd/MM/yyyy',
            'attr' => ['class' => 'js-datepicker'],
        ]);
    }
}
