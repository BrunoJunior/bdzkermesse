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

    /**
     * Les activités d'une kermesse avec réduction du nombre de requêtes
     * @param int $kermesseId
     * @return Activite[] Returns an array of Activite objects
     */
    public function findByKermesseId(int $kermesseId):array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.kermesse', 'k')
            ->addSelect('k')
            ->andWhere('k.id = :kermesseId')
            ->setParameter('kermesseId', $kermesseId)
            ->orderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Kermesse $kermesse
     * @return Activite|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findCaisseCentrale(Kermesse $kermesse): ?Activite
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.caisse_centrale = 1')
            ->andWhere('a.kermesse = :kermesse')
            ->setParameter('kermesse', $kermesse)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
