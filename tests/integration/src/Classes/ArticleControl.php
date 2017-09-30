<?php

namespace Tests\Integration\Classes;

use Arachne\ParameterValidation\Rules\Validate;
use Nette\Application\UI\Control;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Expression;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticleControl extends Control
{
    /**
     * @Validate(parameter = "entity.id", constraints = @EqualTo(1))
     * @Validate(parameter = "id", constraints = @EqualTo(2))
     */
    public function handleDelete(ArticleEntity $entity, $id): void
    {
    }

    /**
     * @Validate(constraints = @Expression("value.from < value.to"))
     */
    public function handleExpression($from, $to): void
    {
    }
}
