<?php

namespace App\DataTransfer;

use Symfony\Component\Validator\Constraints as Assert;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use ZipCodeValidator\Constraints\ZipCode;


/**
 * Class DemandeInscription
 * @package App\DataTransfer
 */
class DemandeInscription
{
    /**
     * @Assert\Email()
     * @var string
     */
    public $email;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $etablissement;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $contact;

    /**
     * @AssertPhoneNumber(defaultRegion="FR", type="mobile")
     * @var string
     */
    public $mobile;

    /**
     * @var string
     */
    public $role;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $localite;

    /**
     * @ZipCode(iso="FR")
     * @var string
     */
    public $codePostal;
}
