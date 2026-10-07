<?php

namespace App\DataFixtures;

use App\Entity\Agent;
use App\Entity\Espece;
use App\Entity\Lot;
use App\Entity\Pepiniere;
use App\Entity\Planche;
use App\Entity\SuiviPerte;
use App\Entity\User;
use App\Enum\CausePerte;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = (new User())
            ->setNom('LOUKOU')
            ->setPrenoms('Kouakou Maxime')
            ->setRoles(["ROLE_ADMIN","ROLE_USER"])
            ->setEmail('lmaxkof@gmail.com')
            ->setPassword(password_hash('123456789', PASSWORD_BCRYPT));
            $manager->persist($user);

        $agent = (new Agent())
            ->setNom('Kouassi Yao')
            ->setFonction('Technicien pepinieriste')
            ->setTelephone('+225 07 00 00 00')
            ->setEmail('kouassi@foret.ci');
        $manager->persist($agent);

        $espece = (new Espece())
            ->setNomCommun('Teck')
            ->setNomScientifique('Tectona grandis')
            ->setFamille('Lamiaceae');
        $manager->persist($espece);

        $pepiniere = (new Pepiniere())
            ->setCodePepiniere('PEP-001')
            ->setNomPepiniere('Pepiniere de Yamoussoukro')
            ->setRegion('Lacs')
            ->setDepartement('Yamoussoukro')
            ->setVillage('Kossou')
            ->setLatitude('6.8276000')
            ->setLongitude('-5.2893000')
            ->setSuperficieM2('1500.00')
            ->setOrganismeGestionnaire('SODEFOR')
            ->setResponsable($agent)
            ->setDateCreation(new \DateTimeImmutable('2025-01-15'))
            ->setCapaciteProductionAn(50000);
        $manager->persist($pepiniere);

        $planche = (new Planche())
            ->setPepiniere($pepiniere)
            ->setNumeroPlanche('P-01')
            ->setCodeQr('QR-PEP001-P01');
        $manager->persist($planche);

        $lot = (new Lot())
            ->setNumeroLot('LOT-2025-001')
            ->setPepiniere($pepiniere)
            ->setPlanche($planche)
            ->setEspece($espece)
            ->setDateSemis(new \DateTimeImmutable('2025-02-01'))
            ->setNbGrainesSemees(1000)
            ->setDateGermination(new \DateTimeImmutable('2025-02-20'))
            ->setNbPlantsLeves(820)
            ->setDateSortie(new \DateTimeImmutable('2025-06-01'));
        $manager->persist($lot);

        $perte = (new SuiviPerte())
            ->setLot($lot)
            ->setDatePerte(new \DateTimeImmutable('2025-03-10'))
            ->setCause(CausePerte::Champignons)
            ->setNbPlantsPerdus(40)
            ->setPourcentage('4.88')
            ->setObservations('Fonte des semis apres fortes pluies');
        $manager->persist($perte);

        $manager->flush();
    }
}
