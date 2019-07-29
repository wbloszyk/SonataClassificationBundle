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

namespace Sonata\ClassificationBundle\Block\Service;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Christian Gripp <mail@core23.de>
 */
abstract class AbstractCategoriesBlockService extends AbstractClassificationBlockService
{
    /**
     * @var CategoryManagerInterface
     */
    private $categoryManager;

    /**
     * @var AdminInterface
     */
    private $categoryAdmin;

    /**
     * @param string $name
     */
    public function __construct($name, EngineInterface $templating, ContextManagerInterface $contextManager, CategoryManagerInterface $categoryManager, AdminInterface $categoryAdmin)
    {
        parent::__construct($name, $templating, $contextManager);

        $this->categoryManager = $categoryManager;
        $this->categoryAdmin = $categoryAdmin;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $category = $this->getCategory($blockContext->getSetting('categoryId'), $blockContext->getSetting('category'));
        $root = $this->categoryManager->getRootCategory($blockContext->getSetting('context'));

        return $this->renderResponse($blockContext->getTemplate(), [
            'context' => $blockContext,
            'settings' => $blockContext->getSettings(),
            'block' => $blockContext->getBlock(),
            'category' => $category,
            'root' => $root,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block): void
    {
        $adminField = $this->getFormAdminType($formMapper, $this->categoryAdmin, 'categoryId', 'category', [
            'label' => 'form.label_category',
        ], [
            'translation_domain' => 'SonataClassificationBundle',
            'link_parameters' => [
                [
                    [
                        'context' => $block->getSetting('context'),
                    ],
                ],
            ],
        ]);

        $formMapper->add(ImmutableArrayType::class, [
                'keys' => [
                    ['title', TextType::class, [
                        'required' => false,
                        'label' => 'form.label_title',
                    ]],
                    ['translation_domain', TextType::class, [
                        'label' => 'form.label_translation_domain',
                        'required' => false,
                    ]],
                    ['icon', TextType::class, [
                        'label' => 'form.label_icon',
                        'required' => false,
                    ]],
                    ['class', TextType::class, [
                        'label' => 'form.label_class',
                        'required' => false,
                    ]],
                    ['context', ChoiceType::class, [
                        'label' => 'form.label_context',
                        'required' => false,
                        'choices' => $this->getContextChoices(),
                    ]],
                    [$adminField, null, []],
                ],
                'translation_domain' => 'SonataClassificationBundle',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => null,
            'translation_domain' => null,
            'icon' => 'fa fa-folder-open-o',
            'class' => null,
            'category' => false,
            'categoryId' => null,
            'context' => 'default',
            'template' => '@SonataClassification/Block/base_block_categories.html.twig',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block): void
    {
        if (is_numeric($block->getSetting('categoryId'))) {
            $block->setSetting('categoryId', $this->getCategory($block->getSetting('categoryId')));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(BlockInterface $block): void
    {
        $this->resolveIds($block);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(BlockInterface $block): void
    {
        $this->resolveIds($block);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        $description = (null !== $code ? $code : $this->getName());

        return new Metadata($this->getName(), $description, false, 'SonataClassificationBundle', [
            'class' => 'fa fa-folder-open-o',
        ]);
    }

    /**
     * @param CategoryInterface|int $id
     * @param mixed                 $default
     *
     * @return CategoryInterface
     */
    final protected function getCategory($id, $default = null)
    {
        if ($id instanceof CategoryInterface) {
            return $id;
        }

        if (is_numeric($id)) {
            return $this->categoryManager->find($id);
        }

        if ($default instanceof CategoryInterface) {
            return $default;
        }

        return null;
    }

    private function resolveIds(BlockInterface $block): void
    {
        $block->setSetting(
            'categoryId',
            \is_object($block->getSetting('categoryId')) ? $block->getSetting('categoryId')->getId() : null
        );
    }
}
