<?php

namespace Arachne\ParameterValidation\DI;

use Arachne\ParameterValidation\Rules\Validate;
use Arachne\ParameterValidation\Rules\ValidateRuleHandler;
use Arachne\Verifier\DI\VerifierExtension;
use Nette\DI\CompilerExtension;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ParameterValidationExtension extends CompilerExtension
{
    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('handler'))
            ->setType(ValidateRuleHandler::class)
            ->addTag(
                VerifierExtension::TAG_HANDLER,
                [
                    Validate::class,
                ]
            );
    }
}
