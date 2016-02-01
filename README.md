# Application web basique avec Authentification

Voici un exemple d'application sécurisée à l'aide de deux solutions au choix :

* Soit un entre son login + mot de passe qui est confronté à une base de donnée SQL à laquelle est connecté notre application.
* Soit on utilise un serveur CAS, un Service Central d'Authentification.

à propos du serveur CAS, il en existe surement un pour votre école par exemple (cas.icam.fr, cas.polymtl.ca). Ce CAS se chargera de faire remplir à la personne ses identifiants. Enfin, il retournera la personne vers notre application qui récupérera un jeton / ticket qu'il aura alors à faire revalider lui même aurpès de ce CAS. Ainsi la personne sera authentifiée.