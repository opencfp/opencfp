<?php
declare(strict_types=1);

namespace OpenCFP\Domain\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use OpenCFP\Domain\Entity\Airport;

final class AirportRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Airport::class);
    }

    public function withCode(string $code): ?Airport
    {
        $airport = $this->repository->findOneByCode($code);

        if ($airport !== null) {
            return $airport;
        }

        return null;
    }
}
