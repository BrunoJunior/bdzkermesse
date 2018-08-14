<?php

namespace App\Repository;

use App\Entity\Etablissement;
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

    /**
     * @return Kermesse[] Returns an array of Kermesse objects
     */
    public function findByEtablissementOrderByAnnee(Etablissement $etablissement)
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.etablissement = :etablissement')
            ->setParameter('etablissement', $etablissement)
            ->orderBy('k.annee', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Trouver une autre kermesse similaire (même établissement et même année)
     * @param Kermesse $kermesse
     * @return Kermesse|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findSimilarAnotherOne(Kermesse $kermesse): ?Kermesse
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.etablissement = :etablissement')
            ->andWhere('k.annee = :annee')
            ->andWhere('k != :kermesse')
            ->setParameter('etablissement', $kermesse->getEtablissement())
            ->setParameter('annee', $kermesse->getAnnee())
            ->setParameter('kermesse', $kermesse)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
