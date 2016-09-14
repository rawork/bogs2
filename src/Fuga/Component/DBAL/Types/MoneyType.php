<?php

namespace Fuga\Component\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class MoneyType extends Type {
    const MONEY = 'money';

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'DECIMAL(14,2)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (null === $value) ? null : $value;
    }

    public function getName()
    {
        return self::MONEY;
    }
}