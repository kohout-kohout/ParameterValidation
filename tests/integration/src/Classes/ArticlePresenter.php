<?php

namespace Tests\Integration\Classes;

use Arachne\PropertyVerification\Property;
use Nette\Application\UI\Presenter;

/**
 * @author Jáchym Toušek
 */
class ArticlePresenter extends Presenter
{

	/**
	 * @Property(parameter = "entity", property = "id", value = 1)
	 */
	public function actionEdit(ArticleEntity $entity)
	{
	}

}
