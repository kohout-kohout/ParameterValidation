<?php

namespace Tests\Integration\Classes;

use Arachne\PropertyVerification\Property;
use Nette\Application\UI\Presenter;
use Symfony\Component\Validator\Constraints\EqualTo;

/**
 * @author Jáchym Toušek
 */
class ArticlePresenter extends Presenter
{

	/**
	 * @Property(parameter = "entity", property = "id", constraints = @EqualTo( value = 1))
	 */
	public function actionEdit(ArticleEntity $entity)
	{
	}

}
