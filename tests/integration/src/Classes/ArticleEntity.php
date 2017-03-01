<?php

namespace Tests\Integration\Classes;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticleEntity
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
