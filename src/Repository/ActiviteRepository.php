<?php

namespace App\Repository;

use App\Entity\Activite;
use App\Entity\Kermesse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Activite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activite[]    findAll()
 * @method Activite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiviteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Activite::class);
    }

//    /**
//     * @return Activite[] Returns an array of Activite objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
     * @return Activite|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findCaisseCentrale(Kermesse $kermesse): ?Activite
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.nom = :nom')
            ->andWhere('a.kermesse = :kermesse')
            ->setParameter('nom', Activite::NOM_CAISSE_CENT)
            ->setParameter('kermesse', $kermesse)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
