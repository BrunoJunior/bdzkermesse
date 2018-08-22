<?php

namespace App\Repository;

use App\Entity\Membre;
use App\Entity\Remboursement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Remboursement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Remboursement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Remboursement[]    findAll()
 * @method Remboursement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RemboursementRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Remboursement::class);
    }

    /**
     * @param Membre $membre
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countRemboursementsMembres(Membre $membre):int
    {
        return $this->createQueryBuilder('r')
            ->select("COUNT(r)")
            ->andWhere('r.membre = :membre')
            ->setParameter('membre', $membre)
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return Remboursement[] Returns an array of Remboursement objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Remboursement
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
