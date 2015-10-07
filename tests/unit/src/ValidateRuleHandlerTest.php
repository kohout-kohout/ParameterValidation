<?php

namespace Tests\Unit;

use Arachne\ParameterValidation\Exception\ParameterValidationException;
use Arachne\ParameterValidation\Rules\Validate;
use Arachne\ParameterValidation\Rules\ValidateRuleHandler;
use Arachne\Verifier\RuleInterface;
use Codeception\MockeryModule\Test;
use Mockery;
use Mockery\MockInterface;
use Nette\Application\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ValidateRuleHandlerTest extends Test
{

	/** @var ValidateRuleHandler */
	private $handler;

	/** @var MockInterface */
	private $accessor;

	/** @var MockInterface */
	private $validator;

	protected function _before()
	{
		$this->accessor = Mockery::mock(PropertyAccessorInterface::class);
		$this->validator = Mockery::mock(ValidatorInterface::class);
		$this->handler = new ValidateRuleHandler($this->validator, $this->accessor);
	}

	public function testParameterTrue()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter';

		$constraint = new EqualTo();
		$constraint->value = 'property-value';
		$rule->constraints = $constraint;

		$parameters = [
			'parameter' => 'value',
		];
		$request = new Request('Test', 'GET', $parameters);

		$this->setupAccessor($parameters, 'parameter', 'parameter-value');

		$this->validator
			->shouldReceive('validate')
			->with('parameter-value', $constraint)
			->andReturn($this->createViolationsMock());

		$this->assertNull($this->handler->checkRule($rule, $request));
	}

	/**
	 * @expectedException Arachne\ParameterValidation\Exception\ParameterValidationException
	 * @expectedExceptionMessage Parameter 'parameter' does not match the constraints.
	 */
	public function testParameterFalse()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter';

		$constraint = new EqualTo();
		$constraint->value = 'parameter-value';
		$rule->constraints = $constraint;

		$parameters = [
			'parameter' => 'value',
		];
		$request = new Request('Test', 'GET', $parameters);

		$this->setupAccessor($parameters, 'parameter', 'wrong-parameter-value');

		$violations = $this->createViolationsMock(1);

		$this->validator
			->shouldReceive('validate')
			->with('wrong-parameter-value', $constraint)
			->andReturn($violations);

		try {
			$this->handler->checkRule($rule, $request);
		} catch (ParameterValidationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame(null, $e->getComponent());
			$this->assertSame('wrong-parameter-value', $e->getValue());
			$this->assertSame($violations, $e->getViolations());
			throw $e;
		}
	}

	public function testPropertyTrue()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter.property';

		$constraint = new EqualTo();
		$constraint->value = 'property-value';
		$rule->constraints = $constraint;

		$parameters = [
			'parameter' => 'parameter-value',
		];
		$request = new Request('Test', 'GET', $parameters);

		$this->setupAccessor($parameters, 'parameter.property', 'property-value');

		$this->validator
			->shouldReceive('validate')
			->with('property-value', $constraint)
			->andReturn($this->createViolationsMock());

		$this->assertNull($this->handler->checkRule($rule, $request));
	}

	/**
	 * @expectedException Arachne\ParameterValidation\Exception\ParameterValidationException
	 * @expectedExceptionMessage Parameter 'parameter.property' does not match the constraints.
	 */
	public function testPropertyFalse()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter.property';

		$constraint = new EqualTo();
		$constraint->value = 'property-value';
		$rule->constraints = $constraint;

		$parameters = [
			'parameter' => 'parameter-value',
		];
		$request = new Request('Test', 'GET', $parameters);

		$this->setupAccessor($parameters, 'parameter.property', 'wrong-property-value');

		$violations = $this->createViolationsMock(1);

		$this->validator
			->shouldReceive('validate')
			->with('wrong-property-value', $constraint)
			->andReturn($violations);

		try {
			$this->handler->checkRule($rule, $request);
		} catch (ParameterValidationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame(null, $e->getComponent());
			$this->assertSame('wrong-property-value', $e->getValue());
			$this->assertSame($violations, $e->getViolations());
			throw $e;
		}
	}

	/**
	 * @expectedException Arachne\ParameterValidation\Exception\ParameterValidationException
	 * @expectedExceptionMessage Parameter 'component-parameter.property' does not match the constraints.
	 */
	public function testPropertyComponent()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter.property';

		$constraint = new EqualTo();
		$constraint->value = 'property-value';
		$rule->constraints = $constraint;

		$parameters = [
			'component-parameter' => 'parameter-value'
		];
		$request = new Request('Test', 'GET', $parameters);

		$this->setupAccessor($parameters, 'component-parameter.property', 'wrong-property-value');

		$violations = $this->createViolationsMock(1);

		$this->validator
			->shouldReceive('validate')
			->with('wrong-property-value', $constraint)
			->andReturn($violations);

		try {
			$this->handler->checkRule($rule, $request, 'component');
		} catch (ParameterValidationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame('component', $e->getComponent());
			$this->assertSame('wrong-property-value', $e->getValue());
			$this->assertSame($violations, $e->getViolations());
			throw $e;
		}
	}

	public function testMissingParameter()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter';

		$constraint = new EqualTo();
		$constraint->value = null;
		$rule->constraints = $constraint;

		$parameters = [];
		$request = new Request('Test', 'GET', $parameters);

		$this->accessor
			->shouldReceive('isReadable')
			->with(Mockery::on(function ($parameter) use ($parameters) {
				return $parameter == (object) $parameters;
			}), 'parameter')
			->once()
			->andReturn(false);

		$this->validator
			->shouldReceive('validate')
			->with(null, $constraint)
			->andReturn($this->createViolationsMock());

		$this->assertNull($this->handler->checkRule($rule, $request));
	}

	/**
	 * @expectedException Arachne\ParameterValidation\Exception\InvalidArgumentException
	 */
	public function testUnknownAnnotation()
	{
		$rule = Mockery::mock(RuleInterface::class);
		$request = new Request('Test', 'GET', []);

		$this->handler->checkRule($rule, $request);
	}

	/**
	 * @param int $return
	 * @return MockInterface
	 */
	private function createViolationsMock($return = 0)
	{
		$violations = Mockery::mock(ConstraintViolationListInterface::class);
		$violations
			->shouldReceive('count')
			->once()
			->withNoArgs()
			->andReturn($return);
		return $violations;
	}

	/**
	 * @param array $parameters
	 * @param string $property
	 * @param string $return
	 */
	private function setupAccessor(array $parameters, $property, $return)
	{
		$this->accessor
			->shouldReceive('isReadable')
			->with(Mockery::on(function ($parameter) use ($parameters) {
				return $parameter == (object) $parameters;
			}), $property)
			->once()
			->andReturn(true);

		$this->accessor
			->shouldReceive('getValue')
			->with(Mockery::on(function ($parameter) use ($parameters) {
				return $parameter == (object) $parameters;
			}), $property)
			->once()
			->andReturn($return);
	}

	/**
	 * @expectedException Arachne\ParameterValidation\Exception\ParameterValidationException
	 * @expectedExceptionMessage Parameters do not match the constraints.
	 */
	public function testNoParameterException()
	{
		$rule = new Validate();

		$constraint = new EqualTo();
		$constraint->value = 'value';
		$rule->constraints = $constraint;

		$parameters = [
			'parameter' => 'parameter-value',
		];
		$request = new Request('Test', 'GET', $parameters);

		$violations = $this->createViolationsMock(1);

		$this->validator
			->shouldReceive('validate')
			->with(Mockery::on(function ($parameter) use ($parameters) {
				return $parameter == (object) $parameters;
			}), $constraint)
			->andReturn($violations);

		try {
			$this->handler->checkRule($rule, $request);
		} catch (ParameterValidationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame(null, $e->getComponent());
			$this->assertEquals((object) $parameters, $e->getValue());
			$this->assertSame($violations, $e->getViolations());
			throw $e;
		}
	}

}
