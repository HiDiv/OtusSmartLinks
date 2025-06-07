<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'strategy_action')]
#[ORM\Index(name: 'idx_action_tag', columns: ['handler_tag'])]
class Action
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\OneToOne(targetEntity: Strategy::class, inversedBy: 'action')]
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

    public function setStrategy(Strategy $strategy): static
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
