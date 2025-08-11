<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('PUBLIC_ACCESS')"),
        new GetCollection(security: "is_granted('PUBLIC_ACCESS')"),
        new Post(security: "is_granted('ROLE_USER')"),
        new Put(security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_USER')"),
        new Patch(security: "is_granted('ROLE_USER')")
    ],
    normalizationContext: ['groups' => ['blog_post:read']],
    denormalizationContext: ['groups' => ['blog_post:write']]
)]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['blog_post:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['blog_post:read', 'blog_post:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['blog_post:read', 'blog_post:write'])]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['blog_post:read', 'blog_post:write'])]
    private ?string $image = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['blog_post:read', 'blog_post:write'])]
    private ?string $category = null;

    #[ORM\Column]
    #[Groups(['blog_post:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['blog_post:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['blog_post:read', 'blog_post:write'])]
    private ?bool $isPublished = null;

    #[ORM\ManyToOne(inversedBy: 'blogPosts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['blog_post:read', 'blog_post:write'])]
    private ?User $author = null;

    #[ORM\OneToMany(mappedBy: 'blogPost', targetEntity: Comment::class, orphanRemoval: true)]
    #[Groups(['blog_post:read'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'blogPost', targetEntity: Like::class, orphanRemoval: true)]
    #[Groups(['blog_post:read'])]
    private Collection $likes;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isPublished = false;
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setBlogPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getBlogPost() === $this) {
                $comment->setBlogPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setBlogPost($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            if ($like->getBlogPost() === $this) {
                $like->setBlogPost(null);
            }
        }

        return $this;
    }

    public function getLikesCount(): int
    {
        return $this->likes->count();
    }

    public function getApprovedCommentsCount(): int
    {
        return $this->comments->filter(fn($comment) => $comment->isIsApproved())->count();
    }
}
