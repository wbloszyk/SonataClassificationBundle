<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\ClassificationBundle\Tests\Controller\Api;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\ClassificationBundle\Controller\Api\TagController;
use Sonata\ClassificationBundle\Model\TagInterface;
use Sonata\ClassificationBundle\Model\TagManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class TagControllerTest extends TestCase
{
    public function testGetTagsAction(): void
    {
        $paramFetcher = $this->createMock(ParamFetcherInterface::class);
        $paramFetcher->expects($this->once())->method('all')->willReturn([]);

        $pager = $this->createMock(Pager::class);

        $tagManager = $this->createMock(TagManagerInterface::class);
        $tagManager->expects($this->once())->method('getPager')->willReturn($pager);

        $this->assertSame($pager, $this->createTagController($tagManager)->getTagsAction($paramFetcher));
    }

    public function testGetTagAction(): void
    {
        $tag = $this->createMock(TagInterface::class);

        $tagManager = $this->createMock(TagManagerInterface::class);
        $tagManager->expects($this->once())->method('find')->willReturn($tag);

        $this->assertSame($tag, $this->createTagController($tagManager)->getTagAction(1));
    }

    /**
     * @dataProvider getIdsForNotFound
     */
    public function testGetTagNotFoundExceptionAction($identifier, string $message): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage($message);

        $this->createTagController()->getTagAction($identifier);
    }

    /**
     * @phpstan-return list<array{mixed, string}>
     */
    public function getIdsForNotFound(): array
    {
        return [
            [42, 'Tag not found for identifier 42.'],
            ['42', "Tag not found for identifier '42'."],
            [null, 'Tag not found for identifier NULL.'],
            ['', "Tag not found for identifier ''."],
        ];
    }

    public function testPostTagAction(): void
    {
        $tag = $this->createMock(TagInterface::class);

        $tagManager = $this->createMock(TagManagerInterface::class);
        $tagManager->expects($this->once())->method('save')->willReturn($tag);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($tag);
        $form->expects($this->once())->method('all')->willReturn([]);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $view = $this->createTagController($tagManager, $formFactory)->postTagAction(new Request());

        $this->assertInstanceOf(View::class, $view);
    }

    public function testPostTagInvalidAction(): void
    {
        $tag = $this->createMock(TagInterface::class);

        $tagManager = $this->createMock(TagManagerInterface::class);
        $tagManager->expects($this->never())->method('save')->willReturn($tagManager);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(false);
        $form->expects($this->once())->method('all')->willReturn([]);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $view = $this->createTagController($tagManager, $formFactory)->postTagAction(new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testPutTagAction(): void
    {
        $tag = $this->createMock(TagInterface::class);

        $tagManager = $this->createMock(TagManagerInterface::class);
        $tagManager->expects($this->once())->method('find')->willReturn($tag);
        $tagManager->expects($this->once())->method('save')->willReturn($tag);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($tag);
        $form->expects($this->once())->method('all')->willReturn([]);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $view = $this->createTagController($tagManager, $formFactory)->putTagAction(1, new Request());

        $this->assertInstanceOf(View::class, $view);
    }

    public function testPutPostInvalidAction(): void
    {
        $tag = $this->createMock(TagInterface::class);

        $tagManager = $this->createMock(TagManagerInterface::class);
        $tagManager->expects($this->once())->method('find')->willReturn($tag);
        $tagManager->expects($this->never())->method('save')->willReturn($tag);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(false);
        $form->expects($this->once())->method('all')->willReturn([]);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $view = $this->createTagController($tagManager, $formFactory)->putTagAction(1, new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testDeleteTagAction(): void
    {
        $tag = $this->createMock(TagInterface::class);

        $tagManager = $this->createMock(TagManagerInterface::class);
        $tagManager->expects($this->once())->method('find')->willReturn($tag);
        $tagManager->expects($this->once())->method('delete');

        $view = $this->createTagController($tagManager)->deleteTagAction(1);

        $this->assertSame(['deleted' => true], $view);
    }

    public function testDeleteTagInvalidAction(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $tagManager = $this->createMock(TagManagerInterface::class);
        $tagManager->expects($this->once())->method('find')->willReturn(null);
        $tagManager->expects($this->never())->method('delete');

        $this->createTagController($tagManager)->deleteTagAction(1);
    }

    /**
     * Creates a new TagController.
     *
     * @param null $tagManager
     * @param null $formFactory
     *
     * @return TagController
     */
    protected function createTagController($tagManager = null, $formFactory = null)
    {
        if (null === $tagManager) {
            $tagManager = $this->createMock(TagManagerInterface::class);
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock(FormFactoryInterface::class);
        }

        return new TagController($tagManager, $formFactory);
    }
}
