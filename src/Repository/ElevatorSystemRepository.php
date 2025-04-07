<?php

namespace App\Repository;

use App\Entity\ElevatorSystem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ElevatorSystem>
 *
 * @method ElevatorSystem|null find($id, $lockMode = null, $lockVersion = null)
 * @method ElevatorSystem|null findOneBy(array $criteria, array $orderBy = null)
 * @method ElevatorSystem[]    findAll()
 * @method ElevatorSystem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElevatorSystemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElevatorSystem::class);
    }

    public function save(ElevatorSystem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ElevatorSystem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
