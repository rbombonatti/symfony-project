<?php

namespace App\Repository;

use App\Entity\Hashes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hashes>
 *
 * @method Hashes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hashes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hashes[]    findAll()
 * @method Hashes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HashesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hashes::class);
    }

    public function save(Hashes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Hashes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAll()
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }

    public function getNextBlockNumber()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT max(block_number)+1 AS next
            FROM Hashes
            ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $results = $resultSet->fetchOne();
        if ($results === null) return 1;
        return $results;
    }

}
