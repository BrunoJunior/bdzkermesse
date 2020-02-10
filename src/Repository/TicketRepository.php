<?php

namespace App\Repository;

use App\Entity\Kermesse;
use App\Entity\Membre;
use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * Le montant total des dépenses d'une kermesse
     * @param Kermesse $kermesse
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
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
     * @param string $order
     * @return array|Ticket[]
     */
    public function findByKermesse(Kermesse $kermesse, string $order):array
    {
        $sens = 'ASC';
        if ($order[0] === '-') {
            $sens = 'DESC';
            $order = mb_substr($order, 1);
        }
        return $this->createQueryBuilder('t')
            ->innerJoin('t.membre', 'm')
            ->andWhere('t.kermesse = :kermesse')
            ->orderBy('t.' . $order, $sens)
            ->setParameter('kermesse', $kermesse)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Kermesse $kermesse
     * @return ArrayCollection
     * @throws DBALException
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

    /**
     * @param Ticket $ticket
     * @return array
     * @throws DBALException
     */
    public function getTotauxByTicket(Ticket $ticket):array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT COALESCE(SUM(d.montant),0) AS depense, GROUP_CONCAT(a.nom SEPARATOR \', \') AS activites_liees
            FROM ticket AS t 
            LEFT JOIN depense AS d ON (d.ticket_id = t.id)
            INNER JOIN activite AS a ON (d.activite_id = a.id)
            WHERE t.id = :id
        ';
        $stmt = $connection->prepare($sql);
        $stmt->execute(array('id' => $ticket->getId()));
        $resultat = $stmt->fetchAll();
        return empty($resultat) ? ['depense' => 0, 'activites_liees' => ''] : $resultat[0];
    }

    /**
     * Les tickets non remboursés d'un membre
     * @param Membre $membre
     * @return array|Ticket[]
     */
    public function findNonRembourses(Membre $membre):array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.membre = :membre')
            ->andWhere('t.remboursement IS NULL')
            ->orderBy('t.date')
            ->setParameter('membre', $membre)
            ->getQuery()
            ->getResult();
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
