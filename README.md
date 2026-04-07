# SAE301 - Gestion des absences et justificatifs

Application web developpee dans le cadre de la SAE301 pour gerer les absences des etudiants du BUT Informatique (IUT UPHF), de la declaration jusqu'au traitement pedagogique.

## Contexte

La gestion des absences est souvent dispersee entre mails, fichiers et outils heterogenes.
Ce projet centralise le suivi dans une application unique, avec tracabilite des decisions et vue statistique.

## Objectifs du projet

- Simplifier la soumission des justificatifs par les etudiants
- Faciliter le traitement des absences par les responsables pedagogiques
- Donner de la visibilite aux enseignants et equipes pedagogiques
- Produire des statistiques exploitables pour le pilotage

## Fonctionnalites cle

- Authentification et gestion des roles (etudiant, professeur, responsable pedagogique, secretaire)
- Depot et re-soumission de justificatifs
- Validation ou rejet des absences avec suivi des statuts
- Import de donnees via CSV
- Endpoints API pour login, profil, absences, historique et statistiques
- Couverture de tests avec PHPUnit et Behat

## Parcours utilisateurs

### Etudiant

- Consulte ses absences
- Depose un justificatif
- Peut re-soumettre un document si necessaire

### Responsable pedagogique

- Consulte les absences en attente
- Accepte ou refuse les justificatifs
- Suit les cas sensibles (absences repetees, evaluations)

### Professeur / administration

- Consulte les informations metier associees
- Exploite les donnees pour le suivi pedagogique

## Stack technique

- Backend: PHP (autoload Composer, PSR-4)
- Base de donnees: PostgreSQL (PDO)
- Outils qualite: PHPUnit, Behat
- Execution containerisee possible: FrankenPHP (Dockerfile)

## Structure du projet

- public/: point d'entree web
- src/Controllers/: controleurs HTTP et API
- src/Models/: logique metier
- src/Database/: connexion et acces PostgreSQL
- src/Views/: interfaces par role
- tests/: tests unitaires et d'integration
- features/: scenarios BDD Behat
- data/CSV/: jeux d'import
- liquibase/: scripts de migration

## Equipe

SIMSEK Dilara
KHENTACHE Selyane
CHOURAIH Baptiste
MAESSE Oscar
KRZYKAWSKY Roman

Projet realise dans le cadre de la SAE301 (BUT Informatique - IUT UPHF).
