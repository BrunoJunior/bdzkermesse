<?php

namespace App\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Code extends Constraint
{
    public $message = 'Le code "{{ string }}" n\'est pas autorisé. Seuls les caractères alphanumériques, tirets, soulignés et points le sont !';
}
