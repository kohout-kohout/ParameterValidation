<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\ParameterValidation\Exception;

use Arachne\ParameterValidation\Validate;
use Nette\Application\ForbiddenRequestException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Jáchym Toušek
 */
class FailedParameterValidationException extends ForbiddenRequestException
{

	/** @var Validate */
	private $rule;

	/** @var string */
	private $component;

	/** @var mixed */
	private $value;

	/** @var ConstraintViolationListInterface */
	private $violations;

	/**
	 * @return Validate
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
	 * @return mixed
	 */
	public function getViolations()
	{
		return $this->violations;
	}

	/**
	 * @param Validate $rule
	 */
	public function setRule(Validate $rule)
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

	/**
	 * @param ConstraintViolationListInterface $violations
	 */
	public function setViolations(ConstraintViolationListInterface $violations)
	{
		$this->violations = $violations;
	}

}
