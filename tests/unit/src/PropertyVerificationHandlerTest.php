<?php

namespace Tests\Unit;

use Arachne\ParameterValidation\Exception\FailedParameterValidationException;
use Arachne\ParameterValidation\Validate;
use Arachne\ParameterValidation\ParameterValidationHandler;
use Arachne\Verifier\IRule;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;
use Nette\Application\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Jáchym Toušek
 */
class ParameterValidationHandlerTest extends Test
{

	/** @var ParameterValidationHandler */
	private $handler;

	/** @var MockInterface */
	private $accessor;

	/** @var MockInterface */
	private $validator;

	protected function _before()
	{
		$this->accessor = Mockery::mock(PropertyAccessorInterface::class);
		$this->validator = Mockery::mock(ValidatorInterface::class);
		$this->handler = new ParameterValidationHandler($this->validator, $this->accessor);
	}

	public function testParameterTrue()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter';

		$constraint = new EqualTo();
		$constraint->value = 'property-value';
		$rule->constraints = $constraint;

		$request = new Request('Test', 'GET', [
			'parameter' => 'parameter-value'
		]);

		$this->validator
			->shouldReceive('validate')
			->with('parameter-value', $constraint)
			->andReturn($this->createViolationsMock());

		$this->assertNull($this->handler->checkRule($rule, $request));
	}

	/**
	 * @expectedException Arachne\ParameterValidation\Exception\FailedParameterValidationException
	 * @expectedExceptionMessage Parameter 'parameter' does not match the constraints.
	 */
	public function testParameterFalse()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter';

		$constraint = new EqualTo();
		$constraint->value = 'parameter-value';
		$rule->constraints = $constraint;

		$request = new Request('Test', 'GET', [
			'parameter' => 'wrong-parameter-value'
		]);

		$violations = $this->createViolationsMock(1);

		$this->validator
			->shouldReceive('validate')
			->with('wrong-parameter-value', $constraint)
			->andReturn($violations);

		try {
			$this->handler->checkRule($rule, $request);
		} catch (FailedParameterValidationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame(NULL, $e->getComponent());
			$this->assertSame('wrong-parameter-value', $e->getValue());
			$this->assertSame($violations, $e->getViolations());
			throw $e;
		}
	}

	public function testPropertyTrue()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter';
		$rule->property = 'property';

		$constraint = new EqualTo();
		$constraint->value = 'property-value';
		$rule->constraints = $constraint;

		$request = new Request('Test', 'GET', [
			'parameter' => 'parameter-value'
		]);

		$this->accessor
			->shouldReceive('getValue')
			->with('parameter-value', 'property')
			->once()
			->andReturn('property-value');

		$this->validator
			->shouldReceive('validate')
			->with('property-value', $constraint)
			->andReturn($this->createViolationsMock());

		$this->assertNull($this->handler->checkRule($rule, $request));
	}

	/**
	 * @expectedException Arachne\ParameterValidation\Exception\FailedParameterValidationException
	 * @expectedExceptionMessage Property 'property' of parameter 'parameter' does not match the constraints.
	 */
	public function testPropertyFalse()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter';
		$rule->property = 'property';

		$constraint = new EqualTo();
		$constraint->value = 'property-value';
		$rule->constraints = $constraint;

		$request = new Request('Test', 'GET', [
			'parameter' => 'parameter-value'
		]);

		$this->accessor
			->shouldReceive('getValue')
			->with('parameter-value', 'property')
			->once()
			->andReturn('wrong-property-value');

		$violations = $this->createViolationsMock(1);

		$this->validator
			->shouldReceive('validate')
			->with('wrong-property-value', $constraint)
			->andReturn($violations);

		try {
			$this->handler->checkRule($rule, $request);
		} catch (FailedParameterValidationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame(NULL, $e->getComponent());
			$this->assertSame('wrong-property-value', $e->getValue());
			$this->assertSame($violations, $e->getViolations());
			throw $e;
		}
	}

	/**
	 * @expectedException Arachne\ParameterValidation\Exception\FailedParameterValidationException
	 * @expectedExceptionMessage Property 'property' of parameter 'component-parameter' does not match the constraints.
	 */
	public function testPropertyComponent()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter';
		$rule->property = 'property';

		$constraint = new EqualTo();
		$constraint->value = 'property-value';
		$rule->constraints = $constraint;

		$request = new Request('Test', 'GET', [
			'component-parameter' => 'parameter-value'
		]);

		$this->accessor
			->shouldReceive('getValue')
			->with('parameter-value', 'property')
			->once()
			->andReturn('wrong-property-value');

		$violations = $this->createViolationsMock(1);

		$this->validator
			->shouldReceive('validate')
			->with('wrong-property-value', $constraint)
			->andReturn($violations);

		try {
			$this->handler->checkRule($rule, $request, 'component');
		} catch (FailedParameterValidationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame('component', $e->getComponent());
			$this->assertSame('wrong-property-value', $e->getValue());
			$this->assertSame($violations, $e->getViolations());
			throw $e;
		}
	}

	/**
	 * @expectedException Arachne\ParameterValidation\Exception\InvalidArgumentException
	 * @expectedExceptionMessage Missing parameter 'parameter' in given request.
	 */
	public function testPropertyWrongParameter()
	{
		$rule = new Validate();
		$rule->parameter = 'parameter';
		$request = new Request('Test', 'GET', []);

		$this->handler->checkRule($rule, $request);
	}

	/**
	 * @expectedException Arachne\ParameterValidation\Exception\InvalidArgumentException
	 */
	public function testUnknownAnnotation()
	{
		$rule = Mockery::mock(IRule::class);
		$request = new Request('Test', 'GET', []);

		$this->handler->checkRule($rule, $request);
	}

	/**
	 * @param int $count
	 * @return MockInterface
	 */
	private function createViolationsMock($count = 0)
	{
		$violations = Mockery::mock(ConstraintViolationListInterface::class);
		$violations
			->shouldReceive('count')
			->once()
			->withNoArgs()
			->andReturn($count);
		return $violations;
	}

}
