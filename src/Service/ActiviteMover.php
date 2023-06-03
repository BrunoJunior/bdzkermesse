<?php

namespace App\Service;

use App\DataTransfer\MovedActivityDto;
use App\Entity\Activite;
use App\Repository\ActiviteRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service to move an activity to another position
 * It will also chane the order of some other activities
 */
class ActiviteMover
{
    /**
     * @var ActiviteRepository
     */
    private $rActivite;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Dummy constructor
     * @param ActiviteRepository $rActivite
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ActiviteRepository $rActivite, EntityManagerInterface $entityManager)
    {
        $this->rActivite= $rActivite;
        $this->entityManager = $entityManager;
    }

    /**
     * Change the order of the activity with the new position
     * and then change the order of the other activities (depends on the initial position and the new one)
     * finally return all modified activities
     * @param Activite $activite
     * @param int $newPosition
     * @return MovedActivityDto[]
     */
    public function moveActivityTo(Activite $activite, int $newPosition): array {
        $oldPosition = $activite->getOrdre();
        // No change at all ...
        if ($oldPosition === $newPosition) {
            return [];
        }
        // If the new position is greater than the old one, some activities will be move downward
        // else they will be move upward
        $increment = 1;
        if ($oldPosition < $newPosition) {
            $increment = -1;
        }
        // Getting all activities which will move
        $toMove = $this->rActivite->findWillMove($activite->getKermesse()->getId(), $oldPosition, $newPosition);
        $movedActivities = [];
        foreach ($toMove as $activityToMove) {
            $from = $activityToMove->getOrdre();
            // If the activity to move is the initial one, is new position is $newPosition
            // else it will be the old one plus or minus 1 (depends on the difference between the old and the new position)
            if ($activityToMove->getId() === $activite->getId()) {
                $to = $newPosition;
            } else {
                $to = $activityToMove->getOrdre() + $increment;
            }
            $activityToMove->setOrdre($to);
            $movedActivities[] = new MovedActivityDto($activityToMove->getId(), $from, $to);
            $this->entityManager->persist($activityToMove);
        }
        $this->entityManager->flush();
        return $movedActivities;
    }
}