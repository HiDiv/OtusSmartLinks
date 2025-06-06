<?php

namespace App\Entity;

use App\Repository\StrategyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: StrategyRepository::class)]
#[ORM\Table(name: 'strategy')]
#[ORM\Index(name: 'idx_strategy_path_priority', columns: ['path', 'priority'])]
class Strategy
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column]
    private ?int $priority = null;

    /**
     * @var Collection<int, Condition>
     */
    #[ORM\OneToMany(
        targetEntity: Condition::class,
        mappedBy: 'strategy',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $conditions;

    #[ORM\OneToOne(
        targetEntity: Action::class,
        mappedBy: 'strategy',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private ?Action $action = null;

    public function __construct()
    {
        $this->conditions = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return Collection<int, Condition>
     */
    public function getConditions(): Collection
    {
        return $this->conditions;
    }

    public function addCondition(Condition $condition): static
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions->add($condition);
            $condition->setStrategy($this);
        }

        return $this;
    }

    public function removeCondition(Condition $condition): static
    {
        if ($this->conditions->removeElement($condition)) {
            // set the owning side to null (unless already changed)
            if ($condition->getStrategy() === $this) {
                $condition->setStrategy(null);
            }
        }

        return $this;
    }

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(Action $action): static
    {
        // set the owning side of the relation if necessary
        if ($action->getStrategy() !== $this) {
            $action->setStrategy($this);
        }

        $this->action = $action;

        return $this;
    }
}
