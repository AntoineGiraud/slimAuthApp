# Application web basique avec Authentification

Ce projet est une application de base avec un système d'authentification et de gestion des droits d'accès aux pages pour des rôles ou utilisateurs données.

Pour l'authentification, il est possible d'utiliser différentes méthodes :
* Soit notre application va se charger de valider le couple d'identifiants (login / password) envoyé par l'utilisateur
  * en les confrontant au fichier de configuration local
  * à la base de données liée à l'appliation
 qui sera confronté à la configuration locale du site internet
* Soit intéroger un service tiers qui certifiera l'authentification de ce dernier:
  * Service Central d'Authentification (CAS)
    * *à propos du serveur CAS, il en existe surement un pour votre école par exemple (cas.icam.fr, cas.polymtl.ca). Ce CAS se chargera de faire remplir à la personne ses identifiants. Enfin, il retournera la personne vers notre application qui récupérera un jeton / ticket qu'il aura alors à faire revalider lui même aurpès de ce CAS. Ainsi la personne sera authentifiée.*
  * Service LDAP : piste d'amélioration

Bientôt un espace d'administration des utilsateurs sera créé pour modifier les droits est utilisateurs, en ajouter, en supprimer, etc. (inspiration des tutoriels de grafikart: [Gestion d'un espace membre](https://www.grafikart.fr/tutoriels/php/gestion-membre-229), [Gestion d'un espace membre (refactorisation - POO)](https://www.grafikart.fr/tutoriels/php/gestion-membre-poo-632))

## Slim

Le "micro" framework Slim en version 3 est utilisé pour faire tourner l'application et gérer les routes.
Vous retrouverez la documentation sur leur [site internet](http://www.slimframework.com/docs/).

Grafikart avait fait à l'époque [un tutoriel](https://www.grafikart.fr/tutoriels/php/slim-framework-526) sur Slim v2, mais depuis plusieurs choses ont [changé](http://www.slimframework.com/docs/start/upgrade.html)
