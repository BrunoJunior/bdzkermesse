<?php

namespace App\Repository;

use App\Entity\Etablissement;
use App\Entity\TypeActivite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeActivite>
 *
 * @method TypeActivite|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeActivite|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeActivite[]    findAll()
 * @method TypeActivite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeActiviteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeActivite::class);
    }

    public function add(TypeActivite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TypeActivite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Types d’activité pour un établissement (retourne aussi les types communs à tous les établissements)
     * @param Etablissement $etablissement
     * @return array
     */
    public function findByEtablissement(Etablissement $etablissement): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.etablissement = :etablissement')
            ->orWhere('t.etablissement IS NULL')
            ->setParameter('etablissement', $etablissement)
            ->orderBy('t.id', 'DESC')
            ->getQuery()->getResult();
    }

//    /**
//     * @return TypeActivite[] Returns an array of TypeActivite objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    /**
     * Recherche si déjà existante
     * @param Etablissement $etablissement
     * @param string $nom
     * @return TypeActivite|null
     */
    public function findOneByNom(Etablissement $etablissement, string $nom): ?TypeActivite
    {
        $list = $this->createQueryBuilder('t')
            ->where('t.nom = :nom')
            ->andWhere("t.etablissement = :etablissement")
            ->orWhere('t.nom = :nom')
            ->andWhere('t.etablissement IS NULL')
            ->setParameter('nom', $nom)
            ->setParameter('etablissement', $etablissement)
            ->getQuery()
            ->getResult();
        return array_shift($list);
    }
}
