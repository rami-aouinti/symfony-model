<?php

declare(strict_types=1);

namespace App\Tag\Transport\Form\DataTransformer;

use App\Tag\Domain\Entity\Tag;
use App\Tag\Infrastructure\Repository\TagRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Throwable;

use function Symfony\Component\String\u;

/**
 * @package App\Blog\Transport\Form\DataTransformer
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final readonly class TagArrayToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private TagRepository $tags,
    ) {
    }

    public function transform($tags): string
    {
        // The value received is an array of Tag objects generated with
        // Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer::transform()
        // The value returned is a string that concatenates the string representation of those objects

        return implode(',', $tags);
    }

    /**
     * @phpstan-param string|null $string
     *
     * @throws Throwable
     */
    public function reverseTransform($string): array
    {
        if ($string === null || u($string)->isEmpty()) {
            return [];
        }

        $names = array_filter(array_unique($this->trim(u($string)->split(','))));

        /** @var Tag[] $tags */
        $tags = $this->tags->findBy([
            'name' => $names,
        ]);

        $newNames = array_diff($names, $tags);

        foreach ($newNames as $name) {
            $tags[] = new Tag($name);
        }

        return $tags;
    }

    /**
     * @param string[] $strings
     *
     * @return string[]
     */
    private function trim(array $strings): array
    {
        $result = [];

        foreach ($strings as $string) {
            $result[] = trim($string);
        }

        return $result;
    }
}
