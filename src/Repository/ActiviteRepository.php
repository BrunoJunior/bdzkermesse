<?php

namespace App\Repository;

use App\DataTransfer\PlageHoraire;
use App\Entity\Activite;
use App\Entity\Etablissement;
use App\Entity\Kermesse;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Activite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activite[]    findAll()
 * @method Activite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiviteRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
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
            ->orderBy('a.ordre', 'ASC')
            ->addOrderBy('a.nom', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * L'activité d'une kermesse ayant un certain nom
     * @param string $nom
     * @param Kermesse $kermesse
     * @return Activite|null
     * @throws NonUniqueResultException
     */
    public function findParNomPourKermesse(string $nom, Kermesse $kermesse): ?Activite
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.kermesse = :kermesse')
            ->andWhere('a.nom = :nom')
            ->setParameter('kermesse', $kermesse)
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param Kermesse $kermesse
     * @return Activite|null
     * @throws NonUniqueResultException
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

    /**
     * Résultats sous forme d'une liste de tableau contenant
     * 'id', 'recette', 'nombre_ticket', 'depense'
     * @param Kermesse $kermesse
     * @return ArrayCollection
     */
    public function getTotaux(Kermesse $kermesse): ArrayCollection
    {
        // Recettes
        $recettes = $this->createQueryBuilder('a')
            ->leftJoin('a.recettes', 'r')
            ->andWhere('a.kermesse = :kermesse')
            ->setParameter('kermesse', $kermesse)
            ->select('a.id, COALESCE(SUM(r.montant),0) as recette, COALESCE(SUM(r.nombre_ticket),0) as nombre_ticket')
            ->orderBy('a.id')
            ->groupBy('a.id')
            ->getQuery()
            ->getArrayResult();
        // Dépenses
        $depenses = $this->createQueryBuilder('a')
            ->leftJoin('a.depenses', 'd')
            ->andWhere('a.kermesse = :kermesse')
            ->setParameter('kermesse', $kermesse)
            ->select('a.id, COALESCE(SUM(d.montant),0) as depense')
            ->groupBy('a.id')
            ->getQuery()
            ->getArrayResult();
        // Regroupement
        return $this->regrouperDepensesRecettes($recettes, $depenses);
    }

    /**
     * @param Kermesse $kermesse
     * @param string $colonne
     * @return array
     */
    private function getListeIdAccepte(Kermesse $kermesse, string $colonne): array
    {
        $result = $this->createQueryBuilder('a')
            ->andWhere('a.kermesse = :kermesse')
            ->andWhere('a.'.$colonne.' = :accepte')
            ->setParameter('kermesse', $kermesse)
            ->setParameter('accepte', true)
            ->select('a.id')
            ->getQuery()
            ->getArrayResult();
        return array_map(function ($row) {
            return $row['id'];
        }, $result);
    }

    /**
     * Liste des ids d'activités de la kermesse acceptant les tickets
     * @param Kermesse|null $kermesse
     * @return array
     */
    public function getListeIdAccepteTickets(?Kermesse $kermesse): array
    {
        return $kermesse === null ? [] : $this->getListeIdAccepte($kermesse, 'accepte_tickets');
    }

    /**
     * Obtenir la liste des activités d'un établissement non liées à une kermesse
     * pour une année scolaire
     * @param Etablissement $etablissement
     * @param DateTimeInterface|null $date (null = année scolaire en cours)
     * @return array|Activite[]
     * @throws Exception
     */
    public function getListeAutres(Etablissement $etablissement, ?DateTimeInterface $date = null): array
    {
        return $this->andWheresActions($this->createQueryBuilder('a'), $etablissement, $date)
            ->orderBy('a.date')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param Etablissement $etablissement
     * @param DateTimeInterface|null $date
     * @return QueryBuilder
     * @throws Exception
     */
    private function andWheresActions(QueryBuilder $qb, Etablissement $etablissement, ?DateTimeInterface $date = null): QueryBuilder
    {
        $anneeScolaire = PlageHoraire::createAnneeScolaire($date);
        return $qb->andWhere('a.kermesse IS NULL')
        ->andWhere('a.date >= :debut')->setParameter('debut', $anneeScolaire->getDebut())
        ->andWhere('a.date < :fin')->setParameter('fin', $anneeScolaire->getFin())
        ->andWhere('a.etablissement = :etab')->setParameter('etab', $etablissement);
    }

    /**
     * @param array $depenses
     * @param array $recettes
     * @return ArrayCollection
     */
    private function regrouperDepensesRecettes(array $depenses, array $recettes): ArrayCollection
    {
        $resultat = new ArrayCollection();
        foreach ($recettes as $recette){
            foreach ($depenses as $index => $depense) {
                if ($depense['id'] === $recette['id']) {
                    $resultat->set("".$depense['id'], array_merge($recette, $depense));
                    unset($depenses[$index]);
                    break;
                }
            }
        }
        return $resultat;
    }

    /**
     * Résultats sous forme d'une liste de tableau contenant
     * 'id', 'recette', 'nombre_ticket', 'depense'
     * @param Etablissement $etablissement
     * @param DateTimeInterface|null $date
     * @return ArrayCollection
     * @throws Exception
     */
    public function getTotauxActions(Etablissement $etablissement, ?DateTimeInterface $date = null): ArrayCollection
    {
        // Recettes
        $recettes = $this->andWheresActions($this->createQueryBuilder('a')->leftJoin('a.recettes', 'r'), $etablissement, $date)
            ->select('a.id, COALESCE(SUM(r.montant),0) as recette, COALESCE(SUM(r.nombre_ticket),0) as nombre_ticket')
            ->orderBy('a.id')
            ->groupBy('a.id')
            ->getQuery()
            ->getArrayResult();
        // Dépenses
        $depenses = $this->andWheresActions($this->createQueryBuilder('a')->leftJoin('a.depenses', 'd'), $etablissement, $date)
            ->select('a.id, COALESCE(SUM(d.montant),0) as depense')
            ->groupBy('a.id')
            ->getQuery()
            ->getArrayResult();
        // Regroupement
        return $this->regrouperDepensesRecettes($recettes, $depenses);
    }

    /**
     * Les activités d'une kermesse qui n'ont pas d'ordre
     * @param int $kermesseId
     * @return Activite[] Returns an array of Activite objects
     */
    public function findUnorderedByKermesseId(int $kermesseId):array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.kermesse', 'k')
            ->addSelect('k')
            ->andWhere('k.id = :kermesseId')
            ->andWhere('a.ordre = :ordre')
            ->setParameter('kermesseId', $kermesseId)
            ->setParameter('ordre', 0)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * Get all the activities which will move
     * @param int $kermesseId
     * @param int $from
     * @param int|null $to
     * @return Activite[] All the activities which will move
     */
    public function findWillMove(int $kermesseId, int $from, ?int $to = null): array
    {
        $min = $to === null ? $from : min($from, $to);
        $max = $to === null? null : max($from, $to);
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.kermesse', 'k')
            ->addSelect('k')
            ->andWhere('k.id = :kermesseId')
            ->andWhere('a.ordre >= :min')
            ->setParameter('kermesseId', $kermesseId)
            ->setParameter('min', $min);

        if ($max !== null) {
            $qb->andWhere('a.ordre <= :max')->setParameter('max', $max);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Getting the next available position
     * @param Kermesse $kermesse
     * @return int
     */
    public function getNextPosition(Kermesse $kermesse): int {
        try {
            $result = $this->createQueryBuilder('a')
                ->andWhere('a.kermesse = :kermesse')
                ->setParameter('kermesse', $kermesse)
                ->select('COALESCE(MAX(a.ordre),0) as ordre')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (Exception $exc) {
            return 0;
        }
        return ($result ?? 0) + 1;
    }
}
