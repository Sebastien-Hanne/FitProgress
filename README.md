# FitProgress

FitProgress est une application web de suivi de progression physique. Elle permet à un utilisateur de définir un objectif, renseigner un journal quotidien, consulter son évolution et, s’il le souhaite, demander l’accompagnement d’un coach. Les coachs disposent d’un espace dédié pour gérer leurs demandes, leurs clients, les séances et les commentaires de suivi.

Le projet constitue le MVP réalisé dans le cadre d’un projet de fin de formation DWWM.

## Objectifs du projet

FitProgress centralise dans une même application le suivi du poids, de l’activité, du sommeil, de l’hydratation, de l’alimentation et du ressenti quotidien. Les données restent rattachées au compte qui les a créées et leur consultation par un coach dépend d’une relation de coaching acceptée et du réglage de partage choisi par l’utilisateur.

Trois rôles structurent l’application :

- l’utilisateur suit ses objectifs et son journal ;
- le coach accompagne ses clients autorisés ;
- l’administrateur dispose actuellement d’une route protégée, mais son tableau de bord métier est encore en cours de développement.

## Fonctionnalités

### Utilisateur

- inscription, authentification avec option « se souvenir de moi » et déconnexion ;
- récupération du mot de passe par lien temporaire, sans révéler si une adresse existe ;
- questionnaire initial obligatoire pour créer le profil de suivi ;
- définition et modification d’un objectif de poids, avec historique des objectifs précédents ;
- dashboard de progression : poids, variation, IMC, objectif, rappel de pesée et graphique ;
- journal du jour : poids, humeur, énergie, sommeil, hydratation, activité, notes et repas ;
- historique filtrable et paginé, statistiques et export CSV du journal ;
- consultation du profil, modification des informations générales et gestion de la photo ;
- changement du mot de passe après vérification du mot de passe actuel ;
- changement sécurisé de l’adresse e-mail : mot de passe actuel, adresse en attente, lien valable une heure et notification de l’ancienne adresse après confirmation ;
- réglage du partage des données, suppression du compte avec confirmation du mot de passe, politique de confidentialité et CGU ;
- recherche de coachs par texte ou spécialité, envoi, suivi et annulation d’une demande, changement de coach ou arrêt de l’accompagnement ;
- consultation des commentaires du coach et des séances planifiées ;
- messagerie réservée aux relations de coaching acceptées ;
- centre de notifications avec lecture et suppression.

### Coach

- inscription distincte et création d’un profil professionnel ;
- modification de la biographie, des spécialités, de la localisation, de l’expérience, de la disponibilité et de la capacité d’accueil ;
- consultation, acceptation ou refus des demandes de coaching ;
- liste et recherche des clients accompagnés ;
- accès au journal et aux données d’un client uniquement si la relation est acceptée et si le partage est actif ;
- ajout de commentaires de suivi ;
- messagerie avec les clients accompagnés ;
- planning des séances : création, modification, annulation et passage au statut terminé ;
- notifications automatiques envoyées au client lors de la création ou de la modification d’une séance.

Le modèle de données prévoit un statut de certification et des certificats. En revanche, aucun workflow d’administration permettant de contrôler ou valider ces certifications n’est actuellement finalisé.

### Administrateur

La route `/admin/dashboard` est protégée par `ROLE_ADMIN`. Elle affiche pour le moment un message indiquant que le tableau de bord est en cours de développement. Aucune interface complète d’administration des utilisateurs ou des certifications n’est présentée comme disponible dans ce MVP.

## Règles métier principales

- Un utilisateur standard sans `Goal` est redirigé vers le questionnaire initial avant d’accéder aux pages privées.
- `Goal.initialWeight` représente le poids de départ et n’est pas remplacé par les pesées suivantes.
- Dans le profil, le poids actuel provient exclusivement de la dernière pesée enregistrée dans le journal ; en l’absence de pesée, « Aucune pesée » est affiché. Le poids initial reste utilisé comme valeur de départ pour certains calculs du dashboard et de la page d’objectif.
- Une entrée ancienne du journal est consultable, mais seules les données du jour peuvent être modifiées.
- Les requêtes du journal sont systématiquement limitées à leur propriétaire.
- Un coach ne peut consulter les données de fitness d’un client que si une demande de coaching est acceptée et si le client a activé le partage de son profil.
- Une conversation n’est accessible qu’à ses participants tant que leur relation de coaching est acceptée.
- Seul le coach propriétaire peut modifier l’état de ses séances ; une séance doit être planifiée pour être modifiée, annulée ou terminée.

## Stack technique

Les versions ci-dessous proviennent de l’environnement et des dépendances verrouillées du projet.

| Composant | Version ou usage |
|---|---|
| PHP | 8.4.23 dans l’image Docker (`>= 8.2` dans Composer) |
| Symfony | 7.4.14 LTS |
| Doctrine ORM | 3.6.7 |
| DoctrineBundle | 3.2.4 |
| MariaDB | image Docker MariaDB 11 |
| Twig | 3.27.1 |
| Tailwind CSS | binaire 3.4.19 via SymfonyCasts TailwindBundle 0.14.0 |
| JavaScript | Stimulus 3.2.2 et Turbo 8.0.23 via Importmap |
| Chart.js | 4.5.1 |
| AssetMapper | 7.4.14 |
| KnpPaginatorBundle | 6.10.0 |
| League CSV | 9.28.0 |
| PHPUnit | 13.2.2 |
| Environnement local | Docker Compose, phpMyAdmin et Mailpit |

## Architecture

L’application suit l’organisation Symfony : les Controller orchestrent les requêtes HTTP, les Entity portent le modèle Doctrine, les Repository isolent les requêtes, les Form décrivent les saisies et validations, et les templates Twig produisent les interfaces. Les règles transversales sont notamment portées par les services, les composants Security et les EventSubscriber.

```text
FitProgress/
├── assets/                 # JavaScript, CSS Tailwind et images sources
├── config/                 # Configuration Symfony et sécurité
├── migrations/             # Versions du schéma Doctrine
├── public/                 # Point d’entrée HTTP et fichiers publics
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── EventSubscriber/
│   ├── Form/
│   ├── Repository/
│   ├── Security/
│   └── Service/
├── templates/              # Vues Twig et e-mails
├── tests/                  # Tests fonctionnels, métier et de sécurité
├── compose.yaml
├── composer.json
├── importmap.php
└── package.json
```

## Sécurité

FitProgress s’appuie sur les composants de sécurité Symfony :

- authentification personnalisée et hachage automatique des mots de passe ;
- rôles `ROLE_USER`, `ROLE_COACH` et `ROLE_ADMIN`, avec restrictions par URL ;
- jetons CSRF sur les authentifications et actions sensibles ;
- vérification du propriétaire des journaux, conversations, notifications, demandes et séances ;
- `UserDataVoter` centralisant l’autorisation d’accès d’un coach aux données d’un client ;
- parcours d’onboarding protégé par un EventSubscriber ;
- réinitialisation du mot de passe par jeton temporaire ;
- vérification du mot de passe actuel pour le changement de mot de passe, le changement d’e-mail et la suppression du compte ;
- nouvelle adresse conservée séparément jusqu’à la validation d’un jeton haché valable une heure.

Ces mécanismes réduisent les risques courants, sans constituer une garantie de sécurité absolue. Une revue de sécurité et une configuration de production adaptées restent nécessaires avant tout déploiement public.

## Confidentialité et accessibilité

FitProgress intègre plusieurs mécanismes destinés à protéger les données personnelles et à donner à l’utilisateur un contrôle sur leur utilisation : activation ou retrait du partage avec un coach, export CSV du journal et suppression du compte après vérification du mot de passe.

Une page d’accessibilité enregistre localement dans le navigateur les préférences de taille du texte, contraste élevé, mode sombre, réduction des animations, retours haptiques et assistance vocale. Les interfaces utilisent également des libellés accessibles, des états de focus et une navigation compatible avec le clavier sur les principaux contrôles.

La politique de confidentialité et les CGU incluses dans le projet sont des documents de travail. L’identité, les coordonnées, les durées de conservation et les éventuels sous-traitants doivent être complétés et validés avec les informations réelles de l’éditeur avant une mise en production. Le projet ne revendique pas une conformité RGPD certifiée.

## Installation locale

### Prérequis

- Git ;
- Docker avec Docker Compose ;
- une connexion réseau lors de la première installation des dépendances.

Node.js et npm ne sont pas requis pour lancer l’application avec la chaîne Symfony configurée. Ils ne sont utiles que pour travailler directement avec les dépendances front déclarées dans `package.json`.

### Cloner le dépôt

```bash
git clone --branch dev --single-branch https://github.com/Sebastien-Hanne/FitProgress.git
cd FitProgress
```

La branche distante par défaut `main` contient actuellement la documentation du projet, mais pas l’application Symfony. Le code applicatif publié se trouve sur la branche `dev`, directement à la racine du clone : il n’existe pas de sous-dossier `code/` dans cette branche.

### Configuration

Le fichier `.env` contient les valeurs locales par défaut utilisées par Symfony, Doctrine et Mailer. Pour personnaliser une installation, créer un fichier `.env.local`, qui ne doit jamais être commité :

```bash
cp .env .env.local
```

Ne placez aucun secret de production dans les fichiers suivis par Git.

### Démarrer les services

```bash
docker compose up -d --build
docker compose exec -T php composer install
docker compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec -T php php bin/console tailwind:build
```

Les services locaux sont ensuite accessibles ainsi :

| Service | Accès |
|---|---|
| Application | [http://localhost:8000](http://localhost:8000) |
| phpMyAdmin | [http://localhost:8080](http://localhost:8080) |
| Mailpit | port hôte retourné par `docker compose port mailer 8025` |

Le port de Mailpit est attribué dynamiquement par `compose.override.yaml`. Par exemple, si la commande retourne `0.0.0.0:35731`, son interface se trouve sur `http://localhost:35731`.

Pour arrêter l’environnement sans supprimer les données :

```bash
docker compose down
```

### Assets

AssetMapper et Importmap chargent les modules JavaScript. Les styles sont compilés par le bundle Tailwind :

```bash
docker compose exec -T php php bin/console tailwind:build
```

Pendant un travail sur l’interface :

```bash
docker compose exec php php bin/console tailwind:build --watch
```

## Base de données

Les principales entités sont :

| Entité | Responsabilité |
|---|---|
| `User` | compte, rôles, identité et réglages de partage |
| `Goal` | objectif actif et données physiques initiales |
| `GoalHistory` | archivage des objectifs remplacés |
| `JournalEntry` | mesures et ressenti d’une journée |
| `Meal` | repas rattaché à une entrée du journal |
| `CoachProfile` | présentation, disponibilité et capacité du coach |
| `CoachRequest` | demande et état de la relation de coaching |
| `Session` | rendez-vous planifié entre un coach et un utilisateur |
| `Feedback` | commentaire de suivi rédigé par un coach |
| `Conversation` / `Message` | échanges liés à un accompagnement accepté |
| `Notification` | événements visibles dans le centre de notifications |
| `Certificate` | données de certification prévues par le modèle |
| `ResetPasswordRequest` | jetons temporaires de récupération du mot de passe |

Les évolutions du schéma sont versionnées avec Doctrine Migrations. Le projet contient actuellement **13 migrations disponibles et exécutées** dans l’environnement de développement contrôlé.

## Tests et qualité

L’environnement de test utilise la base distincte `fitprogress_test`, grâce au suffixe configuré dans Doctrine. Pour préparer une installation neuve puis lancer les tests :

```bash
docker compose exec -T php php bin/console doctrine:database:create --env=test --if-not-exists
docker compose exec -T php php bin/console doctrine:migrations:migrate --env=test --no-interaction
docker compose exec -T php php bin/phpunit
```

Dernier résultat vérifié :

```text
44 tests, 173 assertions
100 % réussis
PHPUnit 13.2.2 — PHP 8.4.23
```

Les contrôles utilisés pendant la recette sont :

```bash
docker compose exec -T php php bin/console lint:twig templates
docker compose exec -T php php bin/console lint:yaml config
docker compose exec -T php php bin/console doctrine:schema:validate
docker compose exec -T php php bin/console doctrine:schema:validate --env=test
docker compose exec -T php php bin/phpunit
git diff --check
```

Les tests couvrent notamment l’onboarding, les profils, le journal, l’isolation des données, la mise en relation avec un coach, la messagerie, les notifications et plusieurs règles de l’espace coach.

## E-mails en développement

Les e-mails de bienvenue, de récupération du mot de passe et de confirmation de changement d’adresse sont capturés par Mailpit en développement. Ils ne sont pas envoyés vers un service externe.

Pour connaître l’adresse de l’interface Mailpit de la session Docker :

```bash
docker compose port mailer 8025
```

Mailpit est uniquement un outil de développement et ne constitue pas une solution d’envoi pour la production.

## Aperçu

Le dépôt ne contient pas encore de captures d’écran dédiées à la documentation. Des aperçus du dashboard, du journal et de l’espace coach pourront être ajoutés ici ultérieurement, sans que cela constitue une fonctionnalité annoncée.

## Statut du projet

FitProgress correspond au MVP du projet DWWM. Les parcours utilisateur et coach décrits ci-dessus sont opérationnels et couverts par la recette automatisée actuelle. L’administration complète et la validation administrative des certifications ne font pas partie des fonctionnalités finalisées.

## Auteur et dépôt

Dépôt public : [github.com/Sebastien-Hanne/FitProgress](https://github.com/Sebastien-Hanne/FitProgress)
