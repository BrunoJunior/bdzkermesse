<?php

namespace App\Repository;

use App\Entity\Kermesse;
use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Recette|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recette|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recette[]    findAll()
 * @method Recette[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecetteRepository extends ServiceEntityRepository
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(RegistryInterface $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Recette::class);
        $this->logger = $logger;
    }

    /**
     * Le montant total des recettes ainsi que le nombre total de tickets d'une kermesse
     * @param Kermesse $kermesse
     * @return array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotauxPourKermesse(Kermesse $kermesse):array
    {
        $result = $this->createQueryBuilder('r')
            ->innerJoin('r.activite', 'a')
            ->andWhere('a.kermesse = :kermesse')
            ->setParameter('kermesse', $kermesse)
            ->select('COALESCE(SUM(r.montant),0) as montant, COALESCE(SUM(r.nombre_ticket),0) as nombre_ticket')
            ->getQuery()
            ->getSingleResult();
        $this->logger->debug(print_r($result, true));
        return empty($result) ? ['montant' => 0, 'nombre_ticket' => 0] : $result;
    }

    /**
     * @param Kermesse $kermesse
     * @return array|Recette[]
     */
    public function findByKermesse(Kermesse $kermesse):array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.activite', 'a')
            ->andWhere('a.kermesse = :kermesse')
            ->orderBy('r.date')
            ->setParameter('kermesse', $kermesse)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Recette[] Returns an array of Recette objects
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
    public function findOneBySomeField($value): ?Recette
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
