<?php

declare(strict_types=1);

namespace Tests\Integration;

use Arachne\Codeception\Module\NetteDIModule;
use Arachne\Verifier\Verifier;
use Codeception\Test\Unit;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Tests\Integration\Classes\ArticleEntity;
use Tests\Integration\Classes\ArticlePresenter;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ValidateRuleHandlerTest extends Unit
{
    /**
     * @var NetteDIModule
     */
    protected $tester;

    /**
     * @var Verifier
     */
    private $verifier;

    public function _before(): void
    {
        $this->verifier = $this->tester->grabService(Verifier::class);
    }

    public function testActionEditAllowed(): void
    {
        $request = new Request(
            'Article',
            'GET',
            [
                Presenter::ACTION_KEY => 'edit',
                'entity' => new ArticleEntity(1),
                'id' => 2,
            ]
        );

        self::assertTrue($this->verifier->isLinkVerified($request, new ArticlePresenter()));
    }

    public function testActionEditDisallowed(): void
    {
        $request = new Request(
            'Article',
            'GET',
            [
                Presenter::ACTION_KEY => 'edit',
                'entity' => new ArticleEntity(2),
                'id' => 2,
            ]
        );

        self::assertFalse($this->verifier->isLinkVerified($request, new ArticlePresenter()));
    }

    public function testInnerRules(): void
    {
        $request = new Request(
            'Article',
            'GET',
            [
                Presenter::ACTION_KEY => 'innerrules',
                'id' => 1,
            ]
        );

        self::assertFalse($this->verifier->isLinkVerified($request, new ArticlePresenter()));
    }

    public function testComponentSignalAllowed(): void
    {
        $request = new Request(
            'Article',
            'GET',
            [
                Presenter::ACTION_KEY => 'default',
                Presenter::SIGNAL_KEY => 'article-delete',
                'article-entity' => new ArticleEntity(1),
                'article-id' => 2,
            ]
        );

        self::assertTrue($this->verifier->isLinkVerified($request, (new ArticlePresenter())->getComponent('article')));
    }

    public function testComponentSignalDisallowed(): void
    {
        $request = new Request(
            'Article',
            'GET',
            [
                Presenter::ACTION_KEY => 'default',
                Presenter::SIGNAL_KEY => 'article-delete',
                'article-entity' => new ArticleEntity(2),
                'article-id' => 2,
            ]
        );

        self::assertFalse($this->verifier->isLinkVerified($request, (new ArticlePresenter())->getComponent('article')));
    }

    public function testExpressionAllowed(): void
    {
        $request = new Request(
            'Article',
            'GET',
            [
                Presenter::ACTION_KEY => 'expression',
                'from' => 1,
                'to' => 2,
            ]
        );

        self::assertTrue($this->verifier->isLinkVerified($request, new ArticlePresenter()));
    }

    public function testExpressionDisallowed(): void
    {
        $request = new Request(
            'Article',
            'GET',
            [
                Presenter::ACTION_KEY => 'expression',
                'from' => 2,
                'to' => 1,
            ]
        );

        self::assertFalse($this->verifier->isLinkVerified($request, new ArticlePresenter()));
    }

    public function testComponentExpressionAllowed(): void
    {
        $request = new Request(
            'Article',
            'GET',
            [
                Presenter::ACTION_KEY => 'default',
                Presenter::SIGNAL_KEY => 'article-expression',
                'article-from' => 1,
                'article-to' => 2,
            ]
        );

        self::assertTrue($this->verifier->isLinkVerified($request, (new ArticlePresenter())->getComponent('article')));
    }

    public function testComponentExpressionDisallowed(): void
    {
        $request = new Request(
            'Article',
            'GET', [
                Presenter::ACTION_KEY => 'default',
                Presenter::SIGNAL_KEY => 'article-expression',
                'article-from' => 2,
                'article-to' => 1,
            ]
        );

        self::assertFalse($this->verifier->isLinkVerified($request, (new ArticlePresenter())->getComponent('article')));
    }
}
