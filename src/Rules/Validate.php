<?php

namespace Arachne\ParameterValidation\Rules;

use Arachne\Verifier\Rules\ValidationRule;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 *
 * @Annotation
 */
class Validate extends ValidationRule
{

    /** @var string */
    public $parameter;

    /**
     * @todo Don't use fully qualified type.
     * @link http://www.doctrine-project.org/jira/browse/DCOM-253
     * @var \Symfony\Component\Validator\Constraint[]
     */
    public $constraints;

}
