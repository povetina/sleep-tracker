<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $type;

    #[ORM\Column(type: 'datetime_immutable')]
    private $created;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $updated;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $finished;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $started;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'events', cascade: ['persist'])]
    private $tags;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $duration;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCreated(): ?\DateTimeImmutable
    {
        return $this->created;
    }

    #[ORM\PrePersist]
    public function setCreated(): self
    {
        $this->created = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    #[ORM\PreUpdate]
    public function setUpdated(): self
    {
        $this->updated = new \DateTimeImmutable();

        return $this;
    }

    public function getFinished(): ?\DateTimeImmutable
    {
        return $this->finished;
    }

    public function setFinished(?\DateTimeImmutable $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    public function getStarted(): ?\DateTimeImmutable
    {
        return $this->started;
    }

    public function setStarted(?\DateTimeImmutable $started): self
    {
        $this->started = $started;

        return $this;
    }

    public function getTagsNames(): ArrayCollection
    {
        $tagsNames = new ArrayCollection();
        foreach ($this->getTags() as $tag) {
            $tagsNames->add($tag->getName());
        }

        return $tagsNames;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDuration(): self
    {
        $start = $this->getStarted();
        $finish = $this->getFinished();

        $this->duration = $finish->getTimestamp() - $start->getTimestamp();

        return $this;
    }
}
