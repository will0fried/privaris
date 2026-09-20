<?php

namespace App\DataFixtures;

use App\Entity\Constat;
use App\Entity\Entry;
use App\Enum\EntryStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Constats d'exemple pour prévisualiser la refonte de l'accueil.
 * Groupe « constats » : à charger seul, en mode --append (ne purge rien).
 *
 *   php bin/console doctrine:fixtures:load --group=constats --append
 *
 * Rattachés à la dernière entrée publiée. À rééditer / réattacher dans l'admin.
 */
class ConstatFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['constats'];
    }

    public function load(ObjectManager $manager): void
    {
        // On se rattache à la dernière entrée publiée.
        $entry = $manager->getRepository(Entry::class)->findOneBy(
            ['status' => EntryStatus::PUBLISHED],
            ['publishedAt' => 'DESC']
        );

        if (!$entry instanceof Entry) {
            return; // pas d'entrée publiée : rien à démontrer.
        }

        // Idempotent : si un constat existe déjà, on ne recrée rien.
        if (0 !== \count($manager->getRepository(Constat::class)->findAll())) {
            return;
        }

        // De quoi remplir la fiche technique si l'échantillon manque.
        if (null === $entry->getEchantillon()) {
            $entry->setEchantillon('1 réseau domestique');
        }

        $rows = [
            ['C-01', "Une reconnaissance passive suffit à cartographier tous les appareils d'un réseau domestique sans alerter la box.", true, false, '2026-09-08'],
            ['C-02', "La plupart des objets connectés grand public exposent leur constructeur et leur modèle dès le premier scan, avant toute authentification.", false, false, '2026-09-08'],
            ['C-03', "Le nom d'hôte attribué par défaut trahit souvent la marque et la fonction de l'appareil.", false, false, '2026-09-09'],
            ['C-04', "Un port d'administration laissé ouvert reste la porte d'entrée la plus fréquente sur ce type de réseau.", false, false, '2026-09-10'],
            ['C-05', "Le filtrage par adresse MAC de la box suffit à tenir un attaquant déterminé à distance.", false, true, '2026-09-07'],
        ];

        foreach ($rows as [$ref, $enonce, $majeur, $retire, $date]) {
            $c = (new Constat())
                ->setReference($ref)
                ->setEnonce($enonce)
                ->setMajeur($majeur)
                ->setRetire($retire)
                ->setDatePublication(new \DateTimeImmutable($date))
                ->setEntry($entry);
            $manager->persist($c);
        }

        $manager->flush();
    }
}
