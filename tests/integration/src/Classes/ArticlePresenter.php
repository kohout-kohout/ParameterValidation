<?php

namespace Tests\Integration\Classes;

use Arachne\ParameterValidation\Rules\Validate;
use Arachne\Verifier\Rules\All;
use Nette\Application\UI\Presenter;
use Symfony\Component\Validator\Constraints\EqualTo;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticlePresenter extends Presenter
{

	/**
	 * @Validate(parameter = "entity.id", constraints = @EqualTo(value = 1))
	 * @Validate(parameter = "id", constraints = @EqualTo(value = 2))
	 */
	public function actionEdit(ArticleEntity $entity, $id)
	{
	}

	/**
	 * @All({
	 *   @Validate(parameter = "id", constraints = @EqualTo(value = 2))
	 * })
	 */
	public function actionInnerRules($id)
	{
	}

	protected function createComponentArticle()
	{
		return new ArticleControl();
	}

}
