<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\UiBundle\Entity\Form;

class FieldConstraint
{
    private string $code;
    private string $fieldCode;
    private string $fieldValue;

    public function __construct(string $code, string $fieldCode, string $fieldValue)
    {
        $this->code = $code;
        $this->fieldCode = $fieldCode;
        $this->fieldValue = $fieldValue;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getFieldCode(): string
    {
        return $this->fieldCode;
    }

    public function getFieldValue(): string
    {
        return $this->fieldValue;
    }
}
