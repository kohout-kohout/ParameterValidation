<?php

namespace Tests\Integration;

use Arachne\Verifier\Verifier;
use Codeception\TestCase\Test;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Tests\Integration\Classes\ArticleEntity;
use Tests\Integration\Classes\ArticlePresenter;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ValidateRuleHandlerTest extends Test
{

    /** @var Verifier */
    private $verifier;

    public function _before()
    {
        $this->verifier = $this->guy->grabService(Verifier::class);
    }

    public function testActionEditAllowed()
    {
        $request = new Request('Article', 'GET', [
            Presenter::ACTION_KEY => 'edit',
            'entity' => new ArticleEntity(1),
            'id' => 2,
        ]);

        $this->assertTrue($this->verifier->isLinkVerified($request, new ArticlePresenter()));
    }

    public function testActionEditDisallowed()
    {
        $request = new Request('Article', 'GET', [
            Presenter::ACTION_KEY => 'edit',
            'entity' => new ArticleEntity(2),
            'id' => 2,
        ]);

        $this->assertFalse($this->verifier->isLinkVerified($request, new ArticlePresenter()));
    }

    public function testInnerRules()
    {
        $request = new Request('Article', 'GET', [
            Presenter::ACTION_KEY => 'innerrules',
            'id' => 1,
        ]);

        $this->assertFalse($this->verifier->isLinkVerified($request, new ArticlePresenter()));
    }

    public function testComponentSignalAllowed()
    {
        $request = new Request('Article', 'GET', [
            Presenter::ACTION_KEY => 'default',
            Presenter::SIGNAL_KEY => 'article-delete',
            'article-entity' => new ArticleEntity(1),
            'article-id' => 2,
        ]);

        $this->assertTrue($this->verifier->isLinkVerified($request, (new ArticlePresenter())->getComponent('article')));
    }

    public function testComponentSignalDisallowed()
    {
        $request = new Request('Article', 'GET', [
            Presenter::ACTION_KEY => 'default',
            Presenter::SIGNAL_KEY => 'article-delete',
            'article-entity' => new ArticleEntity(2),
            'article-id' => 2,
        ]);

        $this->assertFalse($this->verifier->isLinkVerified($request, (new ArticlePresenter())->getComponent('article')));
    }

    public function testExpressionAllowed()
    {
        $request = new Request('Article', 'GET', [
            Presenter::ACTION_KEY => 'expression',
            'from' => 1,
            'to' => 2,
        ]);

        $this->assertTrue($this->verifier->isLinkVerified($request, new ArticlePresenter()));
    }

    public function testExpressionDisallowed()
    {
        $request = new Request('Article', 'GET', [
            Presenter::ACTION_KEY => 'expression',
            'from' => 2,
            'to' => 1,
        ]);

        $this->assertFalse($this->verifier->isLinkVerified($request, new ArticlePresenter()));
    }

    public function testComponentExpressionAllowed()
    {
        $request = new Request('Article', 'GET', [
            Presenter::ACTION_KEY => 'default',
            Presenter::SIGNAL_KEY => 'article-expression',
            'article-from' => 1,
            'article-to' => 2,
        ]);

        $this->assertTrue($this->verifier->isLinkVerified($request, (new ArticlePresenter())->getComponent('article')));
    }

    public function testComponentExpressionDisallowed()
    {
        $request = new Request('Article', 'GET', [
            Presenter::ACTION_KEY => 'default',
            Presenter::SIGNAL_KEY => 'article-expression',
            'article-from' => 2,
            'article-to' => 1,
        ]);

        $this->assertFalse($this->verifier->isLinkVerified($request, (new ArticlePresenter())->getComponent('article')));
    }

}
