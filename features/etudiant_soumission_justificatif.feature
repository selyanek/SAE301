Feature: Soumettre un justificatif d'absence (Etudiant)
  En tant qu'etudiant
  Je veux soumettre un justificatif d'absence
  Afin de faire valider mon absence comme excusee

  Scenario: Affichage du formulaire de soumission
    Given que l'etudiant est connecte a son espace personnel
    When il accede a la section Soumettre un justificatif
    Then le systeme affiche un formulaire a remplir

  Scenario: Affichage des cours concernes
    Given que l'etudiant a rempli correctement le formulaire
    When il saisit des dates d'absence valides
    Then le systeme affiche automatiquement les cours concernes

  Scenario: Limite de soumission a 48h
    Given que l'etudiant est connecte et que sa date de retour date de plus de 48 heures
    When il tente de soumettre son justificatif
    Then la soumission est refusee avec un message d'erreur de depassement de delai

  Scenario: Soumission reussie avec email de confirmation
    Given que l'etudiant a fini de remplir le formulaire d'absence dans le delai autorise
    When il appuie sur le bouton Soumettre
    Then le justificatif est enregistre et un email de confirmation avec le tag [GESTION-ABS] est envoye
