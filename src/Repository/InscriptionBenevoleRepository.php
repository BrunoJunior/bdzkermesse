<?php

namespace App\Repository;

use App\Entity\InscriptionBenevole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InscriptionBenevole|null find($id, $lockMode = null, $lockVersion = null)
 * @method InscriptionBenevole|null findOneBy(array $criteria, array $orderBy = null)
 * @method InscriptionBenevole[]    findAll()
 * @method InscriptionBenevole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InscriptionBenevoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscriptionBenevole::class);
    }

    // /**
    //  * @return InscriptionBenevole[] Returns an array of InscriptionBenevole objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InscriptionBenevole
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
