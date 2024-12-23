<?php

declare(strict_types=1);

namespace App\Place\Domain\Entity\Traits;

use App\Place\Domain\Entity\District;
use App\Place\Domain\Entity\Metro;
use App\Place\Domain\Entity\Neighborhood;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait EntityLocationTrait
{
    #[ORM\ManyToOne(targetEntity: District::class, inversedBy: 'properties')]
    #[ORM\JoinColumn(nullable: true)]
    private ?District $district = null;

    #[ORM\ManyToOne(targetEntity: Neighborhood::class, inversedBy: 'properties')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Neighborhood $neighborhood = null;

    #[ORM\ManyToOne(targetEntity: Metro::class, inversedBy: 'properties')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Metro $metro_station = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $address = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $longitude = null;

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): self
    {
        $this->district = $district;

        return $this;
    }

    public function getNeighborhood(): ?Neighborhood
    {
        return $this->neighborhood;
    }

    public function setNeighborhood(?Neighborhood $neighborhood): self
    {
        $this->neighborhood = $neighborhood;

        return $this;
    }

    public function getMetroStation(): ?Metro
    {
        return $this->metro_station;
    }

    public function setMetroStation(?Metro $metro_station): self
    {
        $this->metro_station = $metro_station;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }
}
