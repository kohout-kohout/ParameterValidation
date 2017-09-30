<?php

namespace Arachne\ParameterValidation\Exception;

use Arachne\Verifier\Exception\VerificationException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ParameterValidationException extends VerificationException
{
    /**
     * @var string|null
     */
    private $component;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var ConstraintViolationListInterface|null
     */
    private $violations;

    public function getComponent(): ?string
    {
        return $this->component;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getViolations(): ?ConstraintViolationListInterface
    {
        return $this->violations;
    }

    public function setComponent(?string $component): void
    {
        $this->component = $component;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function setViolations(?ConstraintViolationListInterface $violations): void
    {
        $this->violations = $violations;
    }
}
