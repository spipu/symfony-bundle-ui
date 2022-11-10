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
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $fieldCode;

    /**
     * @var string
     */
    private $fieldValue;

    /**
     * @param string $code
     * @param string $fieldCode
     * @param string $fieldValue
     */
    public function __construct(string $code, string $fieldCode, string $fieldValue)
    {
        $this->code = $code;
        $this->fieldCode = $fieldCode;
        $this->fieldValue = $fieldValue;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getFieldCode(): string
    {
        return $this->fieldCode;
    }

    /**
     * @return string
     */
    public function getFieldValue(): string
    {
        return $this->fieldValue;
    }
}
