<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\PropertyVerification\Exception;

use Arachne\PropertyVerification\Property;
use Nette\Application\ForbiddenRequestException;

/**
 * @author Jáchym Toušek
 */
class FailedPropertyVerificationException extends ForbiddenRequestException
{

	/** @var Property */
	private $rule;

	/** @var string */
	private $component;

	/** @var mixed */
	private $value;

	/**
	 * @return Property
	 */
	public function getRule()
	{
		return $this->rule;
	}

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
	 * @param Property $rule
	 */
	public function setRule(Property $rule)
	{
		$this->rule = $rule;
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

}
