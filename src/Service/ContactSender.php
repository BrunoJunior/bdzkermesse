<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 11:32
 */

namespace App\Service;


use App\DataTransfer\ContactDTO;

/**
 * Class ContactSender
 * @package App\Service
 */
class ContactSender extends AbstractSender
{
    /**
     * Sans les extensions
     * @return string
     */
    protected function getTemplate(): string
    {
        return "contact";
    }
}