<?php

namespace App\Repository;

use App\Entity\Etablissement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Etablissement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Etablissement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Etablissement[]    findAll()
 * @method Etablissement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtablissementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etablissement::class);
    }

//    /**
//     * @return Etablissement[] Returns an array of Etablissement objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
     * Recherche d'un établissement avec le couple id / key
     * @param int $id
     * @param string $key
     * @return Etablissement|null
     * @throws NonUniqueResultException
     */
    public function findOneByIdAndKey(int $id, string $key): ?Etablissement
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id = :id')
            ->andWhere('e.resetPwdKey = :key')
            ->setParameter('id', $id)
            ->setParameter('key', $key)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Recherche d'un établissement avec le couple id inscription / key
     * @param int $id
     * @param string $key
     * @return Etablissement|null
     * @throws NonUniqueResultException
     */
    public function findOneByIdInscriptionAndKey(int $id, string $key): ?Etablissement
    {
        return $this->createQueryBuilder('e')
            ->innerJoin('e.originInscription', 'i')
            ->andWhere('i.id = :id')
            ->andWhere('e.resetPwdKey = :key')
            ->setParameter('id', $id)
            ->setParameter('key', $key)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
