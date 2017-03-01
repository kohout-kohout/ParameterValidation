<?php

namespace Arachne\ParameterValidation\DI;

use Arachne\Verifier\DI\VerifierExtension;
use Nette\DI\CompilerExtension;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ParameterValidationExtension extends CompilerExtension
{
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('handler'))
            ->setClass('Arachne\ParameterValidation\Rules\ValidateRuleHandler')
            ->addTag(VerifierExtension::TAG_HANDLER, [
                'Arachne\ParameterValidation\Rules\Validate',
            ]);
    }
}
