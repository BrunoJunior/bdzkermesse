<?php

namespace App\Repository;

use App\Entity\Kermesse;
use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * Le montant total des dépenses d'une kermesse
     * @param Kermesse $kermesse
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getMontantTotalPourKermesse(Kermesse $kermesse):int
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.kermesse = :kermesse')
            ->setParameter('kermesse', $kermesse)
            ->select('COALESCE(SUM(t.montant),0) as montantTotal')
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return Ticket[] Returns an array of Ticket objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
