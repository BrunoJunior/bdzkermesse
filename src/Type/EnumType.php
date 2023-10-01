<?php

namespace App\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EnumType extends Type
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string[]
     */
    protected $values = array();

    /**
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = array_map(function (string $value) {
            return "'$value'";
        }, $this->values);
        return "ENUM(".implode(", ", $values).")";
    }

    /**
     * @param string $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * @param int $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, $this->values)) {
            throw new \InvalidArgumentException("Invalid '".$this->name."' value.");
        }
        return $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param AbstractPlatform $platform
     * @return true
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

}