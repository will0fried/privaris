# Privaris — site (Symfony 6.4)

Carnet de sécurité offensive : site public (one-page) + pages d'articles, et un back-office EasyAdmin pour tout gérer.

- **Front** : Twig + AssetMapper (aucun build Node requis). Design « poste de terrain » (charte v2.0, sombre/ambre).
- **Back** : EasyAdmin — entrées du carnet, matrice de compétences, abonnés newsletter, administrateurs.
- **Base de données** : SQLite en développement, MySQL/MariaDB en production (PlanetHoster).
- PHP **8.1+**.

---

## 1. Installation en local

```bash
# 1. Dépendances PHP
composer install

# 2. Base de données SQLite + schéma + données de départ
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n   # ou : doctrine:schema:create
php bin/console doctrine:fixtures:load -n

# 3. Lancer le serveur
php -S 127.0.0.1:8000 -t public
#   (ou, si tu installes le Symfony CLI : symfony serve)
```

Le site est sur http://127.0.0.1:8000 et le back-office sur http://127.0.0.1:8000/admin.

**Compte admin créé par les fixtures :**
- e-mail : `admin@privaris.fr`
- mot de passe : `PrivarisAdmin!2026` → **à changer immédiatement** (Administrateurs → éditer).

> Pas de migration fournie ? Utilise `php bin/console doctrine:schema:create` (crée les tables directement d'après les entités), puis charge les fixtures.

---

## 2. Le back-office

Accessible sur `/admin` (redirige vers `/connexion` si non connecté).

- **Entrées** : chaque entrée suit la structure du carnet — Objectif, Protocole, Observations, Ce que j'en retiens. Ces quatre champs acceptent un **Markdown léger** : `**gras**`, `` `code` ``, listes `- …`, et surtout des blocs de code entre triples accents graves (```) qui deviennent des blocs terminal colorés sur le site. Le statut (Brouillon / Planifié / Publié) pilote l'affichage : seules les entrées **publiées** ont une page publique ; les autres apparaissent dans le journal avec leur « étiquette de statut ».
- **Compétences** : la matrice / le radar de la page d'accueil. `position` fixe l'ordre.
- **Abonnés** : les inscriptions à la newsletter depuis le site.
- **Administrateurs** : comptes du back-office.

Créer un admin sans fixtures (utile en prod) :

```bash
php bin/console app:create-admin
```

---

## 3. Déploiement sur PlanetHoster (production)

1. **Envoyer le code** (sans `vendor/` ni `var/`) sur le serveur, puis :

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. **Configurer l'environnement** — créer un fichier `.env.local` (non versionné) :

   ```dotenv
   APP_ENV=prod
   APP_SECRET=<une_longue_chaine_aleatoire>
   DATABASE_URL="mysql://UTILISATEUR:MOTDEPASSE@127.0.0.1:3306/NOM_BASE?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
   ```

   Adapte `serverVersion` à la version MariaDB/MySQL de ton hébergement.

3. **Base de données** :

   ```bash
   php bin/console doctrine:migrations:migrate -n   # ou doctrine:schema:create
   php bin/console app:create-admin
   ```

4. **Assets & cache** :

   ```bash
   php bin/console asset-map:compile
   php bin/console cache:clear
   ```

5. **Racine web** : faire pointer le domaine sur le dossier `public/`. Le fichier `public/.htaccess` gère la réécriture d'URL (Apache). Vérifie que `mod_rewrite` est actif.

---

## 4. Structure

```
src/
  Controller/           # Home, Entry (article), Newsletter, Security
  Controller/Admin/     # EasyAdmin : Dashboard + CRUD
  Entity/               # Entry, Skill, Subscriber, User
  Enum/                 # EntryType, EntryStatus
  Repository/
  Twig/ContentExtension # filtre |carnet (Markdown léger → HTML)
  Command/              # app:create-admin
templates/
  home/index.html.twig  # la page one-page
  entry/show.html.twig  # une entrée du carnet
  security/login.html.twig
assets/
  styles/app.css        # la charte v2.0
  js/app.js             # radar, terminal, filtre, barre de progression
public/favicon.svg
```

---

## 5. Notes

- Le logo (marque sonar) est inline dans les templates (`templates/_logo.svg.twig`) ; les fichiers SVG complets (logo clair/sombre, icône, favicon) et la charte graphique v2.0 sont dans le dossier `Documents/Privaris` de ton ordinateur.
- Passage à Symfony 7 possible si l'hébergement offre PHP 8.2+ : adapter les contraintes `6.4.*` → `7.2.*` dans `composer.json`.
- Idées d'évolution : envoi réel de la newsletter, upload d'images pour les articles, catégories/tags, flux RSS du carnet.
