# EquiSuivi

EquiSuivi est une application web de suivi équestre permettant de centraliser les informations liées aux chevaux, aux cavaliers, aux soins, aux compétitions et aux équipements.

## Objectif du projet

L'objectif est de proposer un outil simple et structuré pour suivre l'activité d'un cavalier et de ses chevaux.

L'application permet notamment de gérer :

- Les chevaux ;
- Les cavaliers ;
- Les écuries ;
- Les soins ;
- Les compétitions ;
- Les équipements ;
- Les statistiques de suivi.

## Stack technique

- PHP 8.2
- Symfony 7
- Twig
- Doctrine ORM
- PostgreSQL 18
- SCSS
- Webpack encore
- Bootstrap
- FontAwesome

## Architecture générale

Le projet distingue plusieurs notions métier :

- `AppUser`: utilisateur technique utilisé pour l'authentification ;
- `Rider` : profil métier du cavalier ;
- `Ecurie` : structure équestre à laquelle peuvent être rattachés des cavaliers ;
- `Horse` : cheval suivi dans l'application.

## Installation

Installer les dépendances PHP ;

```bash
composer install
```

Installer les dépendances front :

```bash
npm install
```

Créer la base de données :

```bash
php bin/console doctrine:database:create
```

Exécuter les migrations :

```bash
php bin/console doctrine:migrations:migrate
```

Lancer le serveur Symfony :

```bash
symfony serve
```

Lancer Webpack Encore :

```bash
npm run dev
```

## Authentification

L'application utilise une entité AppUser pour gérer les comptes utilisateurs.

Les rôles prévus sont :

- ROLE_USER : cavalier ;
- ROLE_ECURIE : écurie ;
- ROLE_ADMIN : administrateur .

## Statut du projet

Projet en cours de développement.
