<?php

declare(strict_types=1);

namespace App\Category\Domain\Entity;

use App\Blog\Domain\Entity\Post;
use App\Platform\Domain\Entity\Traits\Timestampable;
use App\Platform\Domain\Entity\Traits\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

/**
 * @package App\Entity
 */
#[ORM\Entity]
#[ORM\Table(name: 'platform_category')]
class Category
{
    use Timestampable;
    use Uuid;

    #[ORM\Id]
    #[ORM\Column(
        name: 'id',
        type: UuidBinaryOrderedTimeType::NAME,
        unique: true,
        nullable: false,
    )]
    #[Groups([
        'Category',
        'Category.id',
    ])]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $name;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'categories')]
    private Collection $posts;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->posts = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): void
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->addCategory($this); // Ajout réciproque
        }
    }

    public function removePost(Post $post): void
    {
        if ($this->posts->removeElement($post)) {
            $post->removeCategory($this); // Suppression réciproque
        }
    }
}
