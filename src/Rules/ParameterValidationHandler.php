<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\ParameterValidation\Rules;

use Arachne\ParameterValidation\Exception\FailedParameterValidationException;
use Arachne\ParameterValidation\Exception\InvalidArgumentException;
use Arachne\Verifier\RuleHandlerInterface;
use Arachne\Verifier\RuleInterface;
use Nette\Application\Request;
use Nette\Object;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Jáchym Toušek
 */
class ParameterValidationHandler extends Object implements RuleHandlerInterface
{

	/** @var ValidatorInterface */
	private $validator;

	/** @var PropertyAccessorInterface */
	private $propertyAccessor;

	public function __construct(ValidatorInterface $validator, PropertyAccessorInterface $propertyAccessor = NULL)
	{
		$this->validator = $validator;
		$this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
	}

	/**
	 * @param RuleInterface $rule
	 * @param Request $request
	 * @throws FailedAuthenticationException
	 */
	public function checkRule(RuleInterface $rule, Request $request, $component = NULL)
	{
		if ($rule instanceof Validate) {
			$this->checkRuleValidate($rule, $request, $component);
		} else {
			throw new InvalidArgumentException('Unknown rule \'' . get_class($rule) . '\' given.');
		}
	}

	/**
	 * @param Validate $rule
	 * @param Request $request
	 * @param string $component
	 * @throws FailedParameterValidationException
	 */
	private function checkRuleValidate(Validate $rule, Request $request, $component)
	{
		$parameters = $request->getParameters();
		$parameter = $component === NULL ? $rule->parameter : $component . '-' . $rule->parameter;
		$value = $this->propertyAccessor->getValue((object) $parameters, $parameter);
		$violations = $this->validator->validate($value, $rule->constraints);
		if ($violations->count()) {
			$message = "Parameter '$parameter' does not match the constraints.";
			$exception = new FailedParameterValidationException($message);
			$exception->setRule($rule);
			$exception->setComponent($component);
			$exception->setValue($value);
			$exception->setViolations($violations);
			throw $exception;
		}
	}

}
