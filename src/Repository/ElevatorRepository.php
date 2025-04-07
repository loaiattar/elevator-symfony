<?php

namespace App\Repository;

use App\Entity\Elevator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Elevator>
 *
 * @method Elevator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Elevator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Elevator[]    findAll()
 * @method Elevator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElevatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Elevator::class);
    }

    public function save(Elevator $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Elevator $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
