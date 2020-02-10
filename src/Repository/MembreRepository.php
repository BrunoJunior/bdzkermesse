<?php

namespace App\Repository;

use App\Entity\Etablissement;
use App\Entity\Membre;
use App\Enum\RemboursementEtatEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Membre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Membre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Membre[]    findAll()
 * @method Membre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MembreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Membre::class);
    }

    /**
     * @param Etablissement $etablissement
     * @return Membre|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findDefautPourEtablissement(Etablissement $etablissement): ?Membre
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.defaut = :defaut')
            ->andWhere('m.etablissement = :etablissement')
            ->setParameter('defaut', true)
            ->setParameter('etablissement', $etablissement)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Etablissement $etablissement
     * @return ArrayCollection id => montant
     */
    public function getMontantsNonRemboursesParMembre(Etablissement $etablissement):ArrayCollection
    {
        $result = $this->createQueryBuilder('m')
            ->leftJoin('m.tickets', 't')
            ->andWhere('m.etablissement = :etablissement')
            ->andWhere('t.remboursement IS NULL')
            ->setParameter('etablissement', $etablissement)
            ->select('m.id, COALESCE(SUM(t.montant),0) as montant')
            ->groupBy('m.id')
            ->getQuery()
            ->getArrayResult();
        $final = new ArrayCollection();
        foreach ($result as $row) {
            $final->set($row['id'], $row['montant']);
        }
        return $final;
    }

    /**
     * @param Etablissement $etablissement
     * @return ArrayCollection id => montant
     */
    public function getMontantsAttenteRemboursementParMembre(Etablissement $etablissement):ArrayCollection
    {
        $result = $this->createQueryBuilder('m')
            ->leftJoin('m.tickets', 't')
            ->leftJoin('t.remboursement', 'r')
            ->andWhere('m.etablissement = :etablissement')
            ->andWhere('r.etat = :etat')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('etat', RemboursementEtatEnum::EN_ATTENTE)
            ->select('m.id, COALESCE(SUM(t.montant),0) as montant')
            ->groupBy('m.id')
            ->getQuery()
            ->getArrayResult();
        $final = new ArrayCollection();
        foreach ($result as $row) {
            $final->set($row['id'], $row['montant']);
        }
        return $final;
    }

    /**
     * @return Membre[] Returns an array of Membre objects
     */
    public function findGestionnairesByEtablissement(Etablissement $etablissement)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.etablissement = :etablissement')
            ->andWhere('m.gestionnaire = :gestionnaire')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('gestionnaire', true)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Membre
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
