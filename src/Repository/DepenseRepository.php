<?php

namespace App\Repository;

use App\Entity\Activite;
use App\Entity\Depense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Depense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Depense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Depense[]    findAll()
 * @method Depense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepenseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Depense::class);
    }

    /**
     * Le montant total des dépenses d'une activité
     * @param Activite $activite
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getMontantTotalPourActivite(Activite $activite):int
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.activite = :activite')
            ->setParameter('activite', $activite)
            ->select('COALESCE(SUM(d.montant),0)')
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return Depense[] Returns an array of Depense objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Depense
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
