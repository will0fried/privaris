<?php

namespace App\DataFixtures;

use App\Entity\Entry;
use App\Entity\Skill;
use App\Entity\User;
use App\Enum\EntryStatus;
use App\Enum\EntryType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadAdmin($manager);
        $this->loadSkills($manager);
        $this->loadEntries($manager);

        $manager->flush();
    }

    private function loadAdmin(ObjectManager $manager): void
    {
        $admin = (new User())
            ->setEmail('admin@privaris.fr')
            ->setDisplayName('Will')
            ->setRoles(['ROLE_ADMIN']);
        // Mot de passe par défaut — À CHANGER dès la première connexion.
        $admin->setPassword($this->hasher->hashPassword($admin, 'PrivarisAdmin!2026'));

        $manager->persist($admin);
    }

    private function loadSkills(ObjectManager $manager): void
    {
        $data = [
            ['Reconnaissance réseau', 'Recon.', 3, 4],
            ['Linux & élévation', 'Linux', 3, 4],
            ['Détection & post-incident', 'Détection', 3, 4],
            ['Rétro-ingénierie', 'Rétro-ing.', 2, 3],
            ['Wi-Fi & sans-fil', 'Wi-Fi', 2, 4],
            ['Web (OWASP)', 'Web', 2, 4],
            ['Active Directory', 'Active Dir.', 1, 4],
            ['Cloud (M365 / AWS)', 'Cloud', 1, 3],
            ['Rapport & pédagogie', 'Rapport', 4, 5],
        ];

        foreach ($data as $i => [$name, $short, $current, $target]) {
            $skill = (new Skill())
                ->setName($name)
                ->setShortLabel($short)
                ->setCurrent($current)
                ->setTarget($target)
                ->setPosition($i);
            $manager->persist($skill);
        }
    }

    private function loadEntries(ObjectManager $manager): void
    {
        // PRV-0001 — Coulisses, en rédaction
        $manager->persist((new Entry())
            ->setReference('PRV-0001')
            ->setType(EntryType::COULISSES)
            ->setStatus(EntryStatus::DRAFT)
            ->setPlanningLabel('En rédaction')
            ->setTitle('Par où je commence, et comment je travaille')
            ->setExcerpt("Le terrain que j'ai choisi pour débuter, la méthode que je m'impose du premier scan jusqu'au rapport, et la règle que je ne franchis jamais.")
            ->setObjectif("Poser les bases de Privaris : le terrain, la méthode, le cadre.")
        );

        // PRV-0002 — Lab, publié, contenu complet
        $protocole = <<<'MD'
Trois étapes, dans l'ordre. On part du plus discret vers le plus bavard.

**1 — Repérer sa propre position.** On identifie son adresse et la plage du réseau.

```bash
$ ip -brief addr
wlan0   UP   192.168.1.42/24
# réseau /24, nous sommes en .42
```

**2 — Lister les machines vivantes.** Un balayage ARP demande « qui est là ? » à toute la plage.

```bash
$ sudo arp-scan --localnet
192.168.1.1     Freebox SAS
192.168.1.23    Raspberry Pi Foundation
192.168.1.51    caméra IP — Hangzhou
# 6 machines, 2 à regarder de près
```

**3 — Interroger les cibles.** Pour chaque machine qui intrigue, nmap révèle services et versions.

```bash
$ nmap -sV -T4 192.168.1.51
23/tcp   open   telnet    (login sans TLS)
554/tcp  open   rtsp      flux vidéo, pas d'auth
# une caméra en telnet + flux ouvert
```
MD;

        $retiens = <<<'MD'
- **La reconnaissance suffit déjà à trouver l'essentiel.** Rien exploité, et pourtant les deux vrais problèmes sautaient aux yeux.
- **Le danger, c'est ce qu'on oublie.** Les appareils qu'on ne regarde plus finissent ouverts.
- **L'ordre des outils compte.** arp-scan pour voir large, nmap pour creuser ciblé.
MD;

        $manager->persist((new Entry())
            ->setReference('PRV-0002')
            ->setType(EntryType::LAB)
            ->setStatus(EntryStatus::PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable('2026-09-08'))
            ->setTitle('Cartographier un réseau domestique en 20 minutes')
            ->setExcerpt("Avant de chercher la moindre faille, il faut savoir ce qu'il y a sur le réseau. La méthode exacte pour dresser la carte d'un réseau maison — reproductible chez vous.")
            ->setObjectif("Produire une **carte du réseau** : machines vivantes, rôle probable, portes ouvertes. Sur un réseau domestique, cette simple carte révèle déjà l'essentiel des problèmes.")
            ->setProtocole($protocole)
            ->setObservations("En vingt minutes, six appareils apparaissent, dont deux vraies surprises : un Raspberry Pi oublié depuis un projet abandonné, et une caméra IP qui diffusait son flux sans mot de passe.")
            ->setRetiens($retiens)
            ->setVideoUrl('https://www.youtube.com/watch?v=EXEMPLE')
            ->setReadingMinutes(9)
            ->setTerrain('Domestique')
            ->setDuree('22 minutes')
            ->setOutils('nmap, arp-scan')
            ->setPrerequis('Accord écrit')
        );

        // PRV-0003 — Décryptage, planifié
        $manager->persist((new Entry())
            ->setReference('PRV-0003')
            ->setType(EntryType::DECRYPTAGE)
            ->setStatus(EntryStatus::PLANNED)
            ->setPlanningLabel('Planifié · M1')
            ->setTitle('Votre box internet, vue par un attaquant')
            ->setExcerpt("Interface admin, mot de passe par défaut, WPS, UPnP, firmware oublié : la porte d'entrée classique, expliquée à quelqu'un qui n'y connaît rien.")
        );

        // PRV-0004 — Writeup, planifié
        $manager->persist((new Entry())
            ->setReference('PRV-0004')
            ->setType(EntryType::WRITEUP)
            ->setStatus(EntryStatus::PLANNED)
            ->setPlanningLabel('Planifié · M2')
            ->setTitle('TryHackMe : première machine, raisonnement complet en français')
            ->setExcerpt("Pas la solution brute. Le cheminement, les fausses pistes, le temps réellement passé — pour que ça serve à quelqu'un qui apprend comme moi.")
        );
    }
}
