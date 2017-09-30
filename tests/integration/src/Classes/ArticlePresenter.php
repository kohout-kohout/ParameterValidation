<?php

declare(strict_types=1);

namespace Tests\Integration\Classes;

use Arachne\ParameterValidation\Rules\Validate;
use Arachne\Verifier\Rules\All;
use Nette\Application\UI\Presenter;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Expression;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticlePresenter extends Presenter
{
    /**
     * @Validate(parameter = "entity.id", constraints = @EqualTo(1))
     * @Validate(parameter = "id", constraints = @EqualTo(2))
     */
    public function actionEdit(ArticleEntity $entity, $id): void
    {
    }

    /**
     * @All({
     *   @Validate(parameter = "id", constraints = @EqualTo(2))
     * })
     */
    public function actionInnerRules($id): void
    {
    }

    /**
     * @Validate(constraints = @Expression("value.from < value.to"))
     */
    public function actionExpression($from, $to): void
    {
    }

    protected function createComponentArticle(): ArticleControl
    {
        return new ArticleControl();
    }
}
