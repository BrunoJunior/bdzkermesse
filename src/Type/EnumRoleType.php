<?php

namespace App\Type;

class EnumRoleType extends EnumType
{
    const MEMBRE = "membre";
    const PRESIDENT = "president";
    const VICE_PRESIDENT = "vice_president";
    const TRESORIER = "tresorier";
    const TRESORIER_ADJOINT = "tresorier_adjoint";
    const SECRETAIRE = "secretaire";
    const SECRETAIRE_ADJOINT = "secretaire_adjoint";

    protected $name = 'enumrole';
    protected $values = [
        self::MEMBRE,
        self::PRESIDENT,
        self::VICE_PRESIDENT,
        self::TRESORIER,
        self::TRESORIER_ADJOINT,
        self::SECRETAIRE,
        self::SECRETAIRE_ADJOINT
    ];
}