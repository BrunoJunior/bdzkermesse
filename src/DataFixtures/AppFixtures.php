<?php

namespace App\DataFixtures;

use App\Entity\Etablissement;
use App\Entity\TypeActivite;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * AppFixtures constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getUserData() as [$nom, $code, $motdepasse, $admin]) {
            $user = new Etablissement();
            $user->setUsername($code);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $motdepasse));
            $user->setNom($nom);
            $user->setAdmin($admin);

            $manager->persist($user);
            $this->addReference($code, $user);
        }

        foreach ($this->getTypeData() as [$id, $lib]) {
            $type = new TypeActivite();
            $type->setNom($lib);

            $manager->persist($type);
            $this->addReference($lib, $type);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    private function getUserData(): array
    {
        return [
            // $userData = [$nom, $code, $motdepasse];
            ['Ecole Notre-Dame', 'nd_st_emilien', 'kitten', true],
            ['Lycée Bernard Palissy', 'bernard_palissy_saintes', 'kitten', false],
        ];
    }

    private function getTypeData(): array {
        return [
            [-1, 'Autre (précisez)'],
            [null, 'Jeu à lot immédiat'],
            [null, 'Jeu à meilleur score'],
            [null, 'Tirage au sort'],
            [null, 'Vente directe'],
            [null, 'Animation'],
            [null, 'Surveillance'],
        ];
    }
}
