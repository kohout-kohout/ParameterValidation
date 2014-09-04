<?php

namespace Tests\Integration\Classes;

use Arachne\EntityLoader\IConverter;
use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class ArticleConverter extends Object implements IConverter
{

	public function canConvert($type)
	{
		return $type === ArticleEntity::class;
	}

	public function entityToParameter($type, $entity)
	{
		return $entity->getId();
	}

	public function parameterToEntity($type, $value)
	{
		return new ArticleEntity($value);
	}

}
