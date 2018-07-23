<?php

namespace App\Repository;

use App\Entity\Kermesse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Kermesse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Kermesse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Kermesse[]    findAll()
 * @method Kermesse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KermesseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Kermesse::class);
    }

//    /**
//     * @return Kermesse[] Returns an array of Kermesse objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('k.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Kermesse
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
