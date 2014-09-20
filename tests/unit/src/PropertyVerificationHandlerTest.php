<?php

namespace Tests\Unit;

use Arachne\PropertyVerification\Exception\FailedPropertyVerificationException;
use Arachne\PropertyVerification\Property;
use Arachne\PropertyVerification\PropertyVerificationHandler;
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
class PropertyVerificationHandlerTest extends Test
{

	/** @var PropertyVerificationHandler */
	private $handler;

	/** @var MockInterface */
	private $accessor;

	/** @var MockInterface */
	private $validator;

	protected function _before()
	{
		$this->accessor = Mockery::mock(PropertyAccessorInterface::class);
		$this->validator = Mockery::mock(ValidatorInterface::class);
		$this->handler = new PropertyVerificationHandler($this->validator, $this->accessor);
	}

	public function testPropertyTrue()
	{
		$rule = new Property();
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
	 * @expectedException Arachne\PropertyVerification\Exception\FailedPropertyVerificationException
	 * @expectedExceptionMessage Property 'property' of parameter 'parameter' does not meet the constraints.
	 */
	public function testPropertyFalse()
	{
		$rule = new Property();
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
		} catch (FailedPropertyVerificationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame(NULL, $e->getComponent());
			$this->assertSame('wrong-property-value', $e->getValue());
			$this->assertSame($violations, $e->getViolations());
			throw $e;
		}
	}

	/**
	 * @expectedException Arachne\PropertyVerification\Exception\FailedPropertyVerificationException
	 * @expectedExceptionMessage Property 'property' of parameter 'component-parameter' does not meet the constraints.
	 */
	public function testPropertyComponent()
	{
		$rule = new Property();
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
		} catch (FailedPropertyVerificationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame('component', $e->getComponent());
			$this->assertSame('wrong-property-value', $e->getValue());
			$this->assertSame($violations, $e->getViolations());
			throw $e;
		}
	}

	/**
	 * @expectedException Arachne\PropertyVerification\Exception\InvalidArgumentException
	 * @expectedExceptionMessage Missing parameter 'parameter' in given request.
	 */
	public function testPropertyWrongParameter()
	{
		$rule = new Property();
		$rule->parameter = 'parameter';
		$request = new Request('Test', 'GET', []);

		$this->handler->checkRule($rule, $request);
	}

	/**
	 * @expectedException Arachne\PropertyVerification\Exception\InvalidArgumentException
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
