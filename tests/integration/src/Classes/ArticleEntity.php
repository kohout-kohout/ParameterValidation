<?php

namespace Tests\Integration\Classes;

use Nette\Object;

/**
 * @author Jáchym Toušek
 */
class ArticleEntity extends Object
{

	private $id;

	public function __construct($id)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

}
