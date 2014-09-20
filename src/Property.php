<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\PropertyVerification;

use Arachne\Verifier\IRule;
use Nette\Object;

/**
 * @author Jáchym Toušek
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Property extends Object implements IRule
{

	/** @var string */
	public $parameter;

	/** @var string */
	public $property;

	/**
	 * @todo Don't use fully qualified type.
	 * @link http://www.doctrine-project.org/jira/browse/DCOM-253
	 * @var \Symfony\Component\Validator\Constraint[]
	 */
	public $constraints;

}
