<?php

declare(strict_types=1);

namespace Arachne\ParameterValidation\Rules;

use Arachne\Verifier\Rules\ValidationRule;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 *
 * @Annotation
 */
class Validate extends ValidationRule
{
    /**
     * @var string
     */
    public $parameter;

    /**
     * @todo Don't use fully qualified type.
     *
     * @see https://github.com/doctrine/annotations/issues/86
     *
     * @var \Symfony\Component\Validator\Constraint[]|\Symfony\Component\Validator\Constraint
     */
    public $constraints;
}
