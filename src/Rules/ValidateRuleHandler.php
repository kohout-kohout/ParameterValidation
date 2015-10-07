<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\ParameterValidation\Rules;

use Arachne\ParameterValidation\Exception\ParameterValidationException;
use Arachne\ParameterValidation\Exception\InvalidArgumentException;
use Arachne\Verifier\RuleHandlerInterface;
use Arachne\Verifier\RuleInterface;
use Nette\Application\Request;
use Nette\Object;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ValidateRuleHandler extends Object implements RuleHandlerInterface
{

	/** @var ValidatorInterface */
	private $validator;

	/** @var PropertyAccessorInterface */
	private $propertyAccessor;

	public function __construct(ValidatorInterface $validator, PropertyAccessorInterface $propertyAccessor = null)
	{
		$this->validator = $validator;
		$this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
	}

	/**
	 * @param RuleInterface $rule
	 * @param Request $request
	 * @param string $component
	 * @throws FailedAuthenticationException
	 */
	public function checkRule(RuleInterface $rule, Request $request, $component = null)
	{
		if (!$rule instanceof Validate) {
			throw new InvalidArgumentException('Unknown rule \'' . get_class($rule) . '\' given.');
		}

		if ($rule->parameter) {
			$parameters = (object) $request->getParameters();
			$parameter = $component ? $component . '-' . $rule->parameter : $rule->parameter;
			$value = $this->propertyAccessor->isReadable($parameters, $parameter) ? $this->propertyAccessor->getValue($parameters, $parameter) : null;
		} elseif ($component) {
			$value = [];
			$prefixLength = strlen($component) + 1;
			foreach ($request->getParameters() as $key => $val) {
				if (substr($key, 0, $prefixLength) === $component . '-') {
					$value[substr($key, $prefixLength)] = $val;
				}
			}
			$value = (object) $value;
		} else {
			$value = (object) $request->getParameters();
		}
		$violations = $this->validator->validate($value, $rule->constraints);
		if ($violations->count()) {
			if ($rule->parameter) {
				$message = "Parameter '$parameter' does not match the constraints.";
			} else {
				$message = "Parameters do not match the constraints.";
			}
			$exception = new ParameterValidationException($rule, $message);
			$exception->setComponent($component);
			$exception->setValue($value);
			$exception->setViolations($violations);
			throw $exception;
		}
	}

}
