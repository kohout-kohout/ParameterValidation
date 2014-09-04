<?php

namespace Tests\Unit;

use Arachne\PropertyVerification\Property;
use Arachne\PropertyVerification\PropertyVerificationHandler;
use Arachne\Verifier\IRule;
use Codeception\TestCase\Test;
use Mockery;
use Mockery\MockInterface;
use Nette\Application\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @author Jáchym Toušek
 */
class PropertyVerificationHandlerTest extends Test
{

	/** @var PropertyVerificationHandler */
	private $handler;

	/** @var MockInterface */
	private $accessor;

	protected function _before()
	{
		$this->accessor = Mockery::mock(PropertyAccessorInterface::class);
		$this->handler = new PropertyVerificationHandler($this->accessor);
	}

	public function testPropertyTrue()
	{
		$rule = new Property();
		$rule->parameter = 'parameter';
		$rule->property = 'property';
		$rule->value = 'property-value';
		$request = new Request('Test', 'GET', [
			'parameter' => 'parameter-value'
		]);

		$this->accessor
			->shouldReceive('getValue')
			->with('parameter-value', 'property')
			->once()
			->andReturn('property-value');

		$this->assertNull($this->handler->checkRule($rule, $request));
	}

	/**
	 * @expectedException Arachne\PropertyVerification\Exception\FailedPropertyVerificationException
	 * @expectedExceptionMessage Property 'property' of parameter 'parameter' does not have the required value.
	 */
	public function testPropertyFalse()
	{
		$rule = new Property();
		$rule->parameter = 'parameter';
		$rule->property = 'property';
		$rule->value = 'property-value';
		$request = new Request('Test', 'GET', [
			'parameter' => 'parameter-value'
		]);

		$this->accessor
			->shouldReceive('getValue')
			->with('parameter-value', 'property')
			->once()
			->andReturn('wrong-property-value');

		try {
			$this->handler->checkRule($rule, $request);
		} catch (FailedPropertyVerificationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame(NULL, $e->getComponent());
			$this->assertSame('wrong-property-value', $e->getValue());
			throw $e;
		}
	}

	/**
	 * @expectedException Arachne\PropertyVerification\Exception\FailedPropertyVerificationException
	 * @expectedExceptionMessage Property 'property' of parameter 'component-parameter' does not have the required value.
	 */
	public function testPropertyComponent()
	{
		$rule = new Property();
		$rule->parameter = 'parameter';
		$rule->property = 'property';
		$rule->value = 'property-value';
		$request = new Request('Test', 'GET', [
			'component-parameter' => 'parameter-value'
		]);

		$this->accessor
			->shouldReceive('getValue')
			->with('parameter-value', 'property')
			->once()
			->andReturn('wrong-property-value');

		try {
			$this->handler->checkRule($rule, $request, 'component');
		} catch (FailedPropertyVerificationException $e) {
			$this->assertSame($rule, $e->getRule());
			$this->assertSame('component', $e->getComponent());
			$this->assertSame('wrong-property-value', $e->getValue());
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

}
