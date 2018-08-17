<?php

namespace App\Repository;

use App\Entity\Activite;
use App\Entity\Kermesse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
}
