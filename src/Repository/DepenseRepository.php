<?php

namespace App\Repository;

use App\Entity\Activite;
use App\Entity\Depense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Depense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Depense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Depense[]    findAll()
 * @method Depense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depense::class);
    }

    /**
     * @return Depense[] Returns an array of Depense objects
     */
    public function findByActivite(Activite $activite)
    {
        return $this->createQueryBuilder('d')
            ->innerJoin('d.ticket', 't')
            ->innerJoin('t.membre', 'm')
            ->addSelect('t')
            ->addSelect('m')
            ->andWhere('d.activite = :activite')
            ->setParameter('activite', $activite)
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Le montant total des dépenses d'une activité
     * @param Activite $activite
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalPourActivite(Activite $activite):int
    {
        $result = $this->createQueryBuilder('d')
            ->andWhere('d.activite = :activite')
            ->setParameter('activite', $activite)
            ->select('COALESCE(SUM(d.montant),0) as montant')
            ->getQuery()
            ->getSingleScalarResult();
        return $result ?? 0;
    }

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
