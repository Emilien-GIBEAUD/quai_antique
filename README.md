# Quai Antique
Ce site est un site vitrine du restaurant Quai Antique.

(Ancien projet ECF devenu un projet fil rouge).

> [NOTE]
> Ce projet contient la partie Back. Il est consommé par la partie Front contenue dans ce [dépôt git](https://github.com/Emilien-GIBEAUD/Quai_Antique_Front).

## Installation
*
*
* 

## Docker
Construction de l'image :

    docker compose build --pull --no-cache

Cette image utilise ces services :
* php (Symfony avec FrankenPHP comme serveur http)
* database (Mariadb)
* phpmyadmin
---------------------------------------------------

Démarrage du container :

    docker compose up -d

En cas de soucis, tenter de vider le cache

    docker builder prune --all --force