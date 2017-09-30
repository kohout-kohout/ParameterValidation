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

    protected function _before(): void
    {
        $this->accessorHandle = Phony::mock(PropertyAccessorInterface::class);
        $this->validatorHandle = Phony::mock(ValidatorInterface::class);
        $this->handler = new ValidateRuleHandler($this->validatorHandle->get(), $this->accessorHandle->get());
    }

    public function testParameterTrue(): void
    {
        $rule = new Validate();
        $rule->parameter = 'parameter';

        $constraint = new EqualTo('property-value');
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

    public function testParameterFalse(): void
    {
        $rule = new Validate();
        $rule->parameter = 'parameter';

        $constraint = new EqualTo('parameter-value');
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
            self::fail();
        } catch (ParameterValidationException $e) {
            self::assertSame('Parameter "parameter" does not match the constraints.', $e->getMessage());
            self::assertSame($rule, $e->getRule());
            self::assertSame(null, $e->getComponent());
            self::assertSame('wrong-parameter-value', $e->getValue());
            self::assertSame($violations, $e->getViolations());
        }
    }

    public function testPropertyTrue(): void
    {
        $rule = new Validate();
        $rule->parameter = 'parameter.property';

        $constraint = new EqualTo('property-value');
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

    public function testPropertyFalse(): void
    {
        $rule = new Validate();
        $rule->parameter = 'parameter.property';

        $constraint = new EqualTo('property-value');
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
            self::fail();
        } catch (ParameterValidationException $e) {
            self::assertSame('Parameter "parameter.property" does not match the constraints.', $e->getMessage());
            self::assertSame($rule, $e->getRule());
            self::assertSame(null, $e->getComponent());
            self::assertSame('wrong-property-value', $e->getValue());
            self::assertSame($violations, $e->getViolations());
        }
    }

    public function testPropertyComponent(): void
    {
        $rule = new Validate();
        $rule->parameter = 'parameter.property';

        $constraint = new EqualTo('property-value');
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
            self::fail();
        } catch (ParameterValidationException $e) {
            self::assertSame('Parameter "component-parameter.property" does not match the constraints.', $e->getMessage());
            self::assertSame($rule, $e->getRule());
            self::assertSame('component', $e->getComponent());
            self::assertSame('wrong-property-value', $e->getValue());
            self::assertSame($violations, $e->getViolations());
        }
    }

    public function testUnknownAnnotation(): void
    {
        $rule = Phony::mock(RuleInterface::class)->get();
        $request = new Request('Test', 'GET', []);

        try {
            $this->handler->checkRule($rule, $request);
            self::fail();
        } catch (InvalidArgumentException $e) {
        }
    }

    private function createViolationsMock(int $return = 0): ConstraintViolationListInterface
    {
        $violationsHandle = Phony::mock(ConstraintViolationListInterface::class);
        $violationsHandle
            ->count
            ->returns($return);

        return $violationsHandle->get();
    }

    private function setupAccessor(array $parameters, string $property, string $return): void
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

    public function testNoParameterException(): void
    {
        $rule = new Validate();

        $constraint = new EqualTo('value');
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
            self::fail();
        } catch (ParameterValidationException $e) {
            self::assertSame('Parameters do not match the constraints.', $e->getMessage());
            self::assertSame($rule, $e->getRule());
            self::assertSame(null, $e->getComponent());
            self::assertEquals((object) $parameters, $e->getValue());
            self::assertSame($violations, $e->getViolations());
        }
    }
}
