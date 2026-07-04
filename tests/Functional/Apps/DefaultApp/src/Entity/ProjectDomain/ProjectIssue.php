<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Entity\ProjectDomain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ProjectIssue implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'projectIssues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\ManyToOne(targetEntity: Developer::class, inversedBy: 'issues')]
    private ?Developer $assignedDeveloper = null;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getAssignedDeveloper(): ?Developer
    {
        return $this->assignedDeveloper;
    }

    public function setAssignedDeveloper(?Developer $assignedDeveloper): void
    {
        $this->assignedDeveloper = $assignedDeveloper;
    }
}
