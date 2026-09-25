# Suivi et gestion de pépinière forestière — Symfony 8.1

Projet Symfony 8.1 (PHP 8.4, Doctrine ORM 3) implémentant le cahier des charges
de suivi et de gestion des pépinières forestières : pépinières, planches, lots
de production, opérations culturales, suivi de croissance, suivi phytosanitaire,
pertes, stock, distribution, dépenses et indicateurs de performance.

## Prérequis

- PHP >= 8.4 (avec extensions `ctype`, `iconv`, `pdo_pgsql` ou `pdo_mysql`)
- Composer 2
- PostgreSQL 14+ (recommandé, PostGIS possible) **ou** MySQL 8 / MariaDB
- Symfony CLI (optionnel, pour le serveur local)

## Installation

```bash
# 1. Installer les dépendances
composer install

# 2. Configurer la base de données
#    Éditez .env (ou créez .env.local) et renseignez DATABASE_URL
cp .env .env.local

# 3. Créer la base
php bin/console doctrine:database:create

# 4. Générer et exécuter la migration à partir des entités
php bin/console make:migration
php bin/console doctrine:migrations:migrate --no-interaction

# 5. (optionnel) Charger les données de démonstration
php bin/console doctrine:fixtures:load --no-interaction

# 6. Lancer le serveur
symfony server:start
# ou
php -S 127.0.0.1:8000 -t public
```

## Structure

```
src/
├── Entity/          15 entités Doctrine (Pepiniere, Lot, Planche, ...)
├── Enum/            EtatSanitaire, CausePerte
├── Repository/      Un repository par entité
├── Controller/      API REST JSON (Pepiniere, Lot, Indicateurs)
└── DataFixtures/    Données de démonstration
config/              Configuration Symfony / Doctrine
migrations/          Migrations Doctrine (générées via make:migration)
```

## Endpoints API

| Méthode | URL                              | Description                          |
|---------|----------------------------------|--------------------------------------|
| GET     | `/`                              | Liste des endpoints                  |
| GET     | `/api/pepinieres`                | Liste des pépinières                 |
| GET     | `/api/pepinieres/{id}`           | Détail d'une pépinière               |
| POST    | `/api/pepinieres`                | Créer une pépinière                  |
| PUT     | `/api/pepinieres/{id}`           | Modifier une pépinière               |
| DELETE  | `/api/pepinieres/{id}`           | Supprimer une pépinière              |
| GET     | `/api/lots`                      | Liste des lots                       |
| GET     | `/api/lots/{id}`                 | Détail d'un lot                      |
| POST    | `/api/lots`                      | Créer un lot                         |
| DELETE  | `/api/lots/{id}`                 | Supprimer un lot                     |
| GET     | `/api/indicateurs/lots`          | Germination / survie / mortalité     |
| GET     | `/api/indicateurs/tableau-bord`  | Synthèse par pépinière et espèce     |
| GET     | `/api/indicateurs/rendement`     | Rendement des pépinières             |

### Exemple

```bash
curl -X POST http://127.0.0.1:8000/api/pepinieres \
  -H 'Content-Type: application/json' \
  -d '{"codePepiniere":"PEP-002","nomPepiniere":"Pépinière de Daloa","region":"Haut-Sassandra","capaciteProductionAn":30000}'
```

## Indicateurs implémentés

- **Taux de germination** : (plants levés ÷ graines semées) × 100 — calculé
  automatiquement à la persistance du lot (`Lot::computeTauxGermination`).
- **Taux de survie** : (plants vivants ÷ plants levés) × 100.
- **Taux de mortalité** : (plants morts ÷ plants levés) × 100.
- **Rendement** : plants distribués ÷ capacité de production.
- **Temps moyen de production** : date de sortie − date de semis.

## Support géospatial (SIG)

Les coordonnées GPS sont stockées en colonnes `latitude` / `longitude`
(`DECIMAL`). Pour une intégration PostGIS complète (types `GEOMETRY`, requêtes
spatiales, QGIS/ArcGIS), ajoutez le paquet `jsor/doctrine-postgis` puis
remplacez les colonnes lat/long par une colonne `geom` de type `point`.

## Notes

- Les entités sont mappées avec des attributs PHP 8 (`#[ORM\...]`).
- L'API n'inclut pas d'authentification : ajoutez `symfony/security-bundle`
  (JWT ou session) avant toute mise en production exposée sur le réseau.
