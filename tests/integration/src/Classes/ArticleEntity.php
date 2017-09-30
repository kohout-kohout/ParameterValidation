<?php

namespace Tests\Integration\Classes;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ArticleEntity
{
    /**
     * @var int
     */
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
