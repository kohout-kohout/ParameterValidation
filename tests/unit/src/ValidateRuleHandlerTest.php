<?php

namespace Tests\Unit;

use Arachne\ParameterValidation\Exception\ParameterValidationException;
use Arachne\ParameterValidation\Rules\Validate;
use Arachne\ParameterValidation\Rules\ValidateRuleHandler;
use Arachne\Verifier\RuleInterface;
use Codeception\Test\Unit;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Phpunit\Phony;
use Nette\Application\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ValidateRuleHandlerTest extends Unit
{
    /**
     * @var ValidateRuleHandler
     */
    private $handler;

    /**
     * @var InstanceHandle
     */
    private $accessorHandle;

    /**
     * @var InstanceHandle
     */
    private $validatorHandle;

    protected function _before()
    {
        $this->accessorHandle = Phony::mock(PropertyAccessorInterface::class);
        $this->validatorHandle = Phony::mock(ValidatorInterface::class);
        $this->handler = new ValidateRuleHandler($this->validatorHandle->get(), $this->accessorHandle->get());
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

        $this->validatorHandle
            ->validate
            ->with('parameter-value', $constraint)
            ->returns($this->createViolationsMock());

        $this->handler->checkRule($rule, $request);
    }

    /**
     * @expectedException \Arachne\ParameterValidation\Exception\ParameterValidationException
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

        $this->validatorHandle
            ->validate
            ->with('wrong-parameter-value', $constraint)
            ->returns($violations);

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

        $this->validatorHandle
            ->validate
            ->with('property-value', $constraint)
            ->returns($this->createViolationsMock());

        $this->handler->checkRule($rule, $request);
    }

    /**
     * @expectedException \Arachne\ParameterValidation\Exception\ParameterValidationException
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

        $this->validatorHandle
            ->validate
            ->with('wrong-property-value', $constraint)
            ->returns($violations);

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
     * @expectedException \Arachne\ParameterValidation\Exception\ParameterValidationException
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
            'component-parameter' => 'parameter-value',
        ];
        $request = new Request('Test', 'GET', $parameters);

        $this->setupAccessor($parameters, 'component-parameter.property', 'wrong-property-value');

        $violations = $this->createViolationsMock(1);

        $this->validatorHandle
            ->validate
            ->with('wrong-property-value', $constraint)
            ->returns($violations);

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

        $this->accessorHandle
            ->isReadable
            ->with($this->createParametersMatcher($parameters), 'parameter')
            ->once()
            ->returns(false);

        $this->validatorHandle
            ->validate
            ->with(null, $constraint)
            ->returns($this->createViolationsMock());

        $this->handler->checkRule($rule, $request);
    }

    /**
     * @expectedException \Arachne\ParameterValidation\Exception\InvalidArgumentException
     */
    public function testUnknownAnnotation()
    {
        $rule = Phony::mock(RuleInterface::class)->get();
        $request = new Request('Test', 'GET', []);

        $this->handler->checkRule($rule, $request);
    }

    /**
     * @param int $return
     *
     * @return ConstraintViolationListInterface
     */
    private function createViolationsMock($return = 0)
    {
        $violationsHandle = Phony::mock(ConstraintViolationListInterface::class);
        $violationsHandle
            ->count
            ->returns($return);

        return $violationsHandle->get();
    }

    /**
     * @param array $parameters
     * @param string $property
     * @param string $return
     */
    private function setupAccessor(array $parameters, $property, $return)
    {
        $this->accessorHandle
            ->isReadable
            ->with($this->createParametersMatcher($parameters), $property)
            ->returns(true);

        $this->accessorHandle
            ->getValue
            ->with($this->createParametersMatcher($parameters), $property)
            ->returns($return);
    }

    /**
     * @expectedException \Arachne\ParameterValidation\Exception\ParameterValidationException
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

        $this->validatorHandle
            ->validate
            ->with($this->createParametersMatcher($parameters), $constraint)
            ->returns($violations);

        try {
            $this->handler->checkRule($rule, $request);
        } catch (ParameterValidationException $e) {
            $this->assertSame($rule, $e->getRule());
            $this->assertSame(null, $e->getComponent());
            $this->assertEquals((object)$parameters, $e->getValue());
            $this->assertSame($violations, $e->getViolations());
            throw $e;
        }
    }

    /**
     * @param array $parameters
     *
     * @return \PHPUnit_Framework_Constraint_Callback
     */
    private function createParametersMatcher(array $parameters)
    {
        return self::callback(
            function ($parameter) use ($parameters) {
                return $parameter == (object) $parameters;
            }
        );
    }
}
