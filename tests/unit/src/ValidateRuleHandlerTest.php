<?php

namespace Tests\Unit;

use Arachne\ParameterValidation\Exception\InvalidArgumentException;
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
            $this->fail();
        } catch (ParameterValidationException $e) {
            self::assertSame('Parameter "parameter" does not match the constraints.', $e->getMessage());
            self::assertSame($rule, $e->getRule());
            self::assertSame(null, $e->getComponent());
            self::assertSame('wrong-parameter-value', $e->getValue());
            self::assertSame($violations, $e->getViolations());
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
            $this->fail();
        } catch (ParameterValidationException $e) {
            self::assertSame('Parameter "parameter.property" does not match the constraints.', $e->getMessage());
            self::assertSame($rule, $e->getRule());
            self::assertSame(null, $e->getComponent());
            self::assertSame('wrong-property-value', $e->getValue());
            self::assertSame($violations, $e->getViolations());
        }
    }

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
            $this->fail();
        } catch (ParameterValidationException $e) {
            self::assertSame('Parameter "component-parameter.property" does not match the constraints.', $e->getMessage());
            self::assertSame($rule, $e->getRule());
            self::assertSame('component', $e->getComponent());
            self::assertSame('wrong-property-value', $e->getValue());
            self::assertSame($violations, $e->getViolations());
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
            ->with(self::equalTo((object) $parameters), 'parameter')
            ->once()
            ->returns(false);

        $this->validatorHandle
            ->validate
            ->with(null, $constraint)
            ->returns($this->createViolationsMock());

        $this->handler->checkRule($rule, $request);
    }

    public function testUnknownAnnotation()
    {
        $rule = Phony::mock(RuleInterface::class)->get();
        $request = new Request('Test', 'GET', []);

        try {
            $this->handler->checkRule($rule, $request);
            $this->fail();
        } catch (InvalidArgumentException $e) {}
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
     * @param array  $parameters
     * @param string $property
     * @param string $return
     */
    private function setupAccessor(array $parameters, $property, $return)
    {
        $this->accessorHandle
            ->isReadable
            ->with(self::equalTo((object) $parameters), $property)
            ->returns(true);

        $this->accessorHandle
            ->getValue
            ->with(self::equalTo((object) $parameters), $property)
            ->returns($return);
    }

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
            ->with(self::equalTo((object) $parameters), $constraint)
            ->returns($violations);

        try {
            $this->handler->checkRule($rule, $request);
            $this->fail();
        } catch (ParameterValidationException $e) {
            self::assertSame('Parameters do not match the constraints.', $e->getMessage());
            self::assertSame($rule, $e->getRule());
            self::assertSame(null, $e->getComponent());
            self::assertEquals((object) $parameters, $e->getValue());
            self::assertSame($violations, $e->getViolations());
        }
    }
}
