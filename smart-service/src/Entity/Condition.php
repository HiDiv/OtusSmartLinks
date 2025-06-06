<?php

namespace App\Entity;

use App\Repository\ConditionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ConditionRepository::class)]
#[ORM\Table(name: 'condition')]
#[ORM\Index(name: 'idx_condition_tag', columns: ['handler_tag'])]
class Condition
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: Strategy::class, inversedBy: 'conditions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Strategy $strategy = null;

    #[ORM\Column(length: 100)]
    private ?string $handlerTag = null;

    #[ORM\Column(nullable: true)]
    private ?array $parameters = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getStrategy(): ?Strategy
    {
        return $this->strategy;
    }

    public function setStrategy(?Strategy $strategy): static
    {
        $this->strategy = $strategy;

        return $this;
    }

    public function getHandlerTag(): ?string
    {
        return $this->handlerTag;
    }

    public function setHandlerTag(string $handlerTag): static
    {
        $this->handlerTag = $handlerTag;

        return $this;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(?array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }
}
