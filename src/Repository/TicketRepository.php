<?php

namespace App\Repository;

use App\Entity\Kermesse;
use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * @param Kermesse $kermesse
     * @return array|Ticket[]
     */
    public function findByKermesse(Kermesse $kermesse):array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.membre', 'm')
            ->andWhere('t.kermesse = :kermesse')
            ->orderBy('t.date')
            ->setParameter('kermesse', $kermesse)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Kermesse $kermesse
     * @return ArrayCollection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTotauxParTicketByKermesse(Kermesse $kermesse):ArrayCollection
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT t.id, COALESCE(SUM(d.montant),0) AS depense, GROUP_CONCAT(a.nom SEPARATOR \', \') AS activites_liees
            FROM ticket AS t 
            LEFT JOIN depense AS d ON (d.ticket_id = t.id)
            INNER JOIN activite AS a ON (d.activite_id = a.id)
            WHERE t.kermesse_id = :id
            GROUP BY t.id
            ORDER BY t.id
        ';
        $stmt = $connection->prepare($sql);
        $stmt->execute(array('id' => $kermesse->getId()));
        $result = new ArrayCollection();
        foreach ($stmt->fetchAll() as $row) {
            $result->set("".$row['id'], $row);
        }
        return $result;
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
