<?php

namespace App\Repository;

use App\Entity\Activite;
use App\Entity\Kermesse;
use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Recette|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recette|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recette[]    findAll()
 * @method Recette[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    /**
     * Le montant total des recettes d'une kermesse
     * @param Kermesse $kermesse
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getMontantTotalPourKermesse(Kermesse $kermesse):int
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.activite', 'a')
            ->andWhere('a.kermesse = :kermesse')
            ->setParameter('kermesse', $kermesse)
            ->select('COALESCE(SUM(r.montant),0) as montantTotal')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Le montant total des recettes d'une activité
     * @param Activite $activite
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getTotauxPourActivite(Activite $activite):array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.activite = :activite')
            ->setParameter('activite', $activite)
            ->select('COALESCE(SUM(r.montant),0) as montant, COALESCE(SUM(r.nombre_ticket),0) as nombre_ticket')
            ->getQuery()
            ->getSingleResult(Query::HYDRATE_ARRAY);
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
