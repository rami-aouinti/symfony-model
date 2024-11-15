<?php

declare(strict_types=1);

namespace App\Tag\Transport\Form\Type;

use App\Tag\Infrastructure\Repository\TagRepository;
use App\Tag\Transport\Form\DataTransformer\TagArrayToStringTransformer;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class TagsInputType
 * @package App\Blog\Transport\Form\Type
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class TagsInputType extends AbstractType
{
    public function __construct(
        private readonly TagRepository $tags,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new CollectionToArrayTransformer(), true)
            ->addModelTransformer(new TagArrayToStringTransformer($this->tags), true)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['tags'] = $this->tags->findAll();
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }
}
