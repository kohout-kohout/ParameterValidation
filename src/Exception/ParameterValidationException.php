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
     * @var string
     */
    private $component;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var ConstraintViolationListInterface
     */
    private $violations;

    /**
     * @return string
     */
    public function getComponent()
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

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @param string $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param ConstraintViolationListInterface $violations
     */
    public function setViolations(ConstraintViolationListInterface $violations)
    {
        $this->violations = $violations;
    }

}
