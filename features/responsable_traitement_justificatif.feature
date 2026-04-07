Feature: Traiter les justificatifs (Responsable pedagogique)
  En tant que responsable pedagogique
  Je veux accepter ou rejeter les justificatifs avec des motifs clairs
  Afin de fournir un retour precis aux etudiants

  Scenario: Acceptation d'un justificatif valide
    Given qu'un justificatif complet et valide est en attente de decision
    When le responsable clique sur Accepter
    Then l'absence passe au statut Excusee et l'etudiant recoit un email de confirmation et le justificatif disparait de la liste d'attente

  Scenario: Rejet avec nouveau motif personnalise
    Given qu'un justificatif est en attente de decision
    When le responsable selectionne Autre motif, saisit Motif non liste et valide le rejet
    Then le motif est ajoute a la liste reutilisable et le rejet est enregistre

  Scenario: Tentative de rejet sans motif
    Given qu'un justificatif est en attente de decision
    When le responsable tente de valider un rejet sans motif
    Then le message Le motif de rejet est obligatoire s'affiche et le rejet n'est pas enregistre
