<?php

namespace App\Repository;

use App\Entity\Etablissement;
use App\Entity\Kermesse;
use App\Entity\Membre;
use App\Entity\Remboursement;
use App\Enum\RemboursementEtatEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method Remboursement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Remboursement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Remboursement[]    findAll()
 * @method Remboursement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RemboursementRepository extends ServiceEntityRepository
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Remboursement::class);
        $this->logger = $logger;
    }

    /**
     * @param Membre $membre
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countRemboursementsMembres(Membre $membre):int
    {
        return $this->createQueryBuilder('r')
            ->select("COUNT(r)")
            ->andWhere('r.membre = :membre')
            ->setParameter('membre', $membre)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Liste des remboursements d'une kermesse
     * @param Kermesse $kermesse
     * @return Remboursement[] Returns an array of Remboursement objects
     */
    public function getListePourKermesse(Kermesse $kermesse):array
    {
        $ids = $this->createQueryBuilder('r')
            ->select("DISTINCT(r)")
            ->leftJoin('r.tickets', 't')
            ->andWhere('t.kermesse = :kermesse')
            ->setParameter('kermesse', $kermesse)
            ->getQuery()
            ->getScalarResult();
        if (empty($ids)) {
            return [];
        }
        $query = $this->createQueryBuilder('r')
            ->andWhere('r.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->addOrderBy('r.date', 'DESC')
            ->getQuery();
        return $query->getResult();
    }

    /**
     * @param Etablissement $etablissement
     * @return ArrayCollection|Remboursement[] Clé : id membre, Valeur : premier remboursement en attente
     */
    public function findPremierEnAttenteParMembre(Etablissement $etablissement): ArrayCollection
    {
        $ids = $this->createQueryBuilder('r')
            ->select("MIN(r.id)")
            ->andWhere('r.etablissement = :etablissement')
            ->andWhere('r.etat = :etat')
            ->groupBy('r.membre')
            ->setParameter('etablissement', $etablissement)
            ->setParameter('etat', RemboursementEtatEnum::EN_ATTENTE)
            ->getQuery()
            ->getScalarResult()
        ;
        $final = new ArrayCollection();
        if (!empty($ids)) {
            $result = $this->createQueryBuilder('r')
                ->andWhere('r.id IN (:ids)')
                ->setParameter('ids', $ids)
                ->getQuery()
                ->getResult();
            foreach ($etablissement->getMembres() as $membre) {
                foreach ($result as $index => $remboursement) {
                    if ($membre == $remboursement->getMembre()) {
                        $final->set($membre->getId(), $remboursement);
                        unset($result[$index]);
                        break;
                    }
                }
            }
        }
        return $final;
    }
}
