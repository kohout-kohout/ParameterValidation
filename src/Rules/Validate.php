<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

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
