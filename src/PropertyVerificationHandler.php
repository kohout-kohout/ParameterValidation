<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\PropertyVerification;

use Arachne\PropertyVerification\Exception\FailedPropertyVerificationException;
use Arachne\PropertyVerification\Exception\InvalidArgumentException;
use Arachne\Verifier\IRule;
use Arachne\Verifier\IRuleHandler;
use Nette\Application\Request;
use Nette\Object;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Jáchym Toušek
 */
class PropertyVerificationHandler extends Object implements IRuleHandler
{

	/** @var PropertyAccessorInterface */
	private $propertyAccessor;

	/** @var ValidatorInterface */
	private $validator;

	public function __construct(ValidatorInterface $validator, PropertyAccessorInterface $propertyAccessor = NULL)
	{
		$this->validator = $validator;
		$this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
	}

	/**
	 * @param IRule $rule
	 * @param Request $request
	 * @throws FailedAuthenticationException
	 */
	public function checkRule(IRule $rule, Request $request, $component = NULL)
	{
		if ($rule instanceof Property) {
			$this->checkRuleProperty($rule, $request, $component);
		} else {
			throw new InvalidArgumentException('Unknown rule \'' . get_class($rule) . '\' given.');
		}
	}

	/**
	 * @param Property $rule
	 * @param Request $request
	 * @param string $component
	 * @throws FailedPropertyVerificationException
	 */
	protected function checkRuleProperty(Property $rule, Request $request, $component)
	{
		$parameters = $request->getParameters();
		$parameter = $component === NULL ? $rule->parameter : $component . '-' . $rule->parameter;
		if (!isset($parameters[$parameter])) {
			throw new InvalidArgumentException("Missing parameter '$parameter' in given request.");
		}
		$value = $this->propertyAccessor->getValue($parameters[$parameter], $rule->property);
		$violations = $this->validator->validate($value, $rule->constraints);
		if ($violations->count()) {
			$exception = new FailedPropertyVerificationException("Property '$rule->property' of parameter '$parameter' does not meet the constraints.");
			$exception->setRule($rule);
			$exception->setComponent($component);
			$exception->setValue($value);
			$exception->setViolations($violations);
			throw $exception;
		}
	}

}
