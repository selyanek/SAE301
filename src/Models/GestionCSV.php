<?php

namespace src\Models;
use Exception;
use PDO;
use src\Database\Database;

// Classe pour gérer la lecture et l'affichage de fichiers CSV
class GestionCSV
{
    public function getCSV($files, $index)
    {
        try {
            if (file_exists($files) !== false) {
                if (($handle = fopen($files, 'r')) !== false) {
                    $dataCSV = [];

                    while (($data = fgetcsv($handle, 100000, ';', '"', '\\')) !== false) {
                        $dataCSV[] = $data;
                    }
                    fclose($handle);
                    for ($i = 1; $i < count($dataCSV); $i++) {
                        echo $dataCSV[$i][$index] . "\n";
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getAllData($files) // Retourne tout le fichier CSV sous la forme d'un tableau de tableaux pour traitement futur
    {
        try {
            if (file_exists($files) !== false) {
                if (($handle = fopen($files, 'r')) !== false) {
                    $dataCSV = [];

                    while (($data = fgetcsv($handle, 100000, ';', '"', '\\')) !== false) {
                        $dataCSV[] = $data;
                    }
                    fclose($handle);
                    return $dataCSV;
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function exportToDB($files, Database $database): ?string
    {
        $csv_array = $this->getAllData($files);
        $pdo = $database->getConnection();
        if (count($csv_array) > 0) {
            array_shift($csv_array);
        }
        $stats = [
            'comptes' => 0,
            'etudiants' => 0,
            'ressources' => 0,
            'professeurs' => 0,
            'cours' => 0,
            'absences' => 0
        ];

        foreach ($csv_array as $row) {
            try {
                $nom = trim($row[0]);
                $prenom = trim($row[1]);
                $formation = trim($row[5]); // Diplômes
                $groupe = isset($row[20]) ? trim($row[20]) : ''; // Groupes
                
                // Si formation est vide, utiliser le groupe
                if (empty($formation) && !empty($groupe)) {
                    $formation = $groupe;
                } elseif (empty($formation)) {
                    $formation = 'Formation non spécifiée';
                }
                // Créer l'identifiant au format prenom.nom
                $identifiant = strtolower($this->normalizeString($prenom) . '.' . $this->normalizeString($nom));
                // 1. Créer ou récupérer le compte étudiant
                $id_etudiant = $this->getOrCreateEtudiant($pdo, $identifiant, $nom, $prenom, $formation, $stats);
                
                // 2. Créer ou récupérer la ressource (matière)
                $matiere = trim($row[12]);
                $id_ressource = $this->getOrCreateRessource($pdo, $matiere, $stats);
                
                // 3. Créer ou récupérer le professeur
                $nomProf = trim($row[22]);
                
                // Si le champ professeur est vide, utiliser le responsable pédagogique comme prof par défaut
                if (empty($nomProf)) {
                    // Utiliser CHRISTELLE ROZE comme prof par défaut
                    $nomProf = 'ROZE CHRISTELLE';
                }
                
                $id_professeur = $this->getOrCreateProfesseur($pdo, $nomProf, $stats);
                
                // 4. Récupérer le responsable pédagogique (on prend le premier disponible)
                $id_responsable = $this->getFirstResponsable($pdo);
                
                // 5. Créer ou récupérer le cours
                $type_cours = trim($row[11]); // CM, TD, TP
                $date_str = trim($row[8]);
                $heure_str = trim($row[9]);
                $duree_str = trim($row[10]);
                $evaluation = isset($row[23]) && trim($row[23]) === 'Oui'; // Vérifier si c'est une évaluation
                
                $d = $this->getDate($date_str, $heure_str);
                $date_debut = date('Y-m-d H:i:s', $d);
                $date_fin = date('Y-m-d H:i:s', $d + $this->convertToSeconds($duree_str));
                
                $id_cours = $this->getOrCreateCours($pdo, $id_ressource, $id_professeur, $id_responsable, 
                                                     $type_cours, $evaluation, $date_debut, $date_fin, $stats);
                
                // 6. Créer l'absence si la personne était absente
                $absent = trim($row[16]) === 'Absence';
                if ($absent) {
                    $motif = trim($row[18]);
                    $justification = trim($row[17]);
                    $justifie = ($justification === 'Absence justifiée');
                    
                    $this->createAbsence($pdo, $id_cours, $id_etudiant, $date_debut, $date_fin, 
                                        $motif, $justifie, $stats);
                }
                
            } catch (Exception $e) {
                error_log("Erreur lors du traitement de la ligne : " . $e->getMessage());
                continue;
            }
        }
        
        return json_encode($stats);
    }
    
    private function normalizeString(string $str): string
    {
        // Normaliser les chaînes pour les identifiants (enlever accents, espaces, etc.)
        $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $str = preg_replace('/[^a-zA-Z0-9]/', '', $str);
        return strtolower($str);
    }
    
    private function generatePassword(): string
    {
        // Générer un mot de passe de 8 caractères (lettres et chiffres)
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < 8; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }
    
    private function getOrCreateEtudiant(PDO $pdo, string $identifiant, string $nom, 
                                         string $prenom, string $formation, array &$stats): int
    {
        // Vérifier si le compte existe déjà
        $sql = "SELECT idCompte FROM Compte WHERE identifiantCompte = :identifiant";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':identifiant' => $identifiant]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['idcompte'])) {
            return (int)$result['idcompte'];
        }
        
        // Créer le compte
        $sql = "INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction) 
                VALUES (:identifiant, :password, :nom, :prenom, 'etudiant')
                RETURNING idCompte";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':identifiant' => $identifiant,
            ':password' => $this->generatePassword(),
            ':nom' => $nom,
            ':prenom' => $prenom
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['comptes']++;
        
        $id_compte = (int)$result['idcompte'];
        
        // Créer le profil étudiant
        $sql = "INSERT INTO Etudiant (idEtudiant, identifiantEtu, formation) 
                VALUES (:id, :identifiant, :formation)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id_compte,
            ':identifiant' => $identifiant,
            ':formation' => $formation
        ]);
        $stats['etudiants']++;
        
        return $id_compte;
    }
    
    private function getOrCreateRessource(PDO $pdo, string $nom, array &$stats): int
    {
        if (empty($nom)) {
            $nom = 'Ressource inconnue';
        }
        
        // Nettoyer le nom de la ressource (corriger l'encodage des apostrophes)
        $nom = str_replace("'", "'", $nom); // Remplacer les apostrophes typographiques
        $nom = html_entity_decode($nom, ENT_QUOTES, 'UTF-8'); // Décoder les entités HTML
        
        // Vérifier si la ressource existe
        $sql = "SELECT idRessource FROM Ressource WHERE nom = :nom";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nom' => $nom]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['idressource'])) {
            return (int)$result['idressource'];
        }
        
        // Créer la ressource
        $sql = "INSERT INTO Ressource (nom) VALUES (:nom) RETURNING idRessource";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nom' => $nom]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['ressources']++;
        
        return (int)$result['idressource'];
    }
    
    private function getOrCreateProfesseur(PDO $pdo, string $nomComplet, array &$stats): int
    {
        // Le champ vide devrait être traité avant d'appeler cette fonction
        // Mais par sécurité, on vérifie quand même
        if (empty($nomComplet)) {
            throw new Exception("Nom de professeur vide détecté");
        }
        
        // Extraire nom et prénom (format: "NOM Prénom")
        $parts = explode(' ', $nomComplet, 2);
        $nom = $parts[0];
        $prenom = isset($parts[1]) ? $parts[1] : '';
        
        $identifiant = strtolower($this->normalizeString($prenom) . '.' . $this->normalizeString($nom));
        
        // Vérifier si le compte existe
        $sql = "SELECT idCompte FROM Compte WHERE identifiantCompte = :identifiant";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':identifiant' => $identifiant]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['idcompte'])) {
            return (int)$result['idcompte'];
        }
        
        // Créer le compte
        $sql = "INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction) 
                VALUES (:identifiant, :password, :nom, :prenom, 'professeur')
                RETURNING idCompte";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':identifiant' => $identifiant,
            ':password' => $this->generatePassword(),
            ':nom' => $nom,
            ':prenom' => $prenom
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['comptes']++;
        
        $id_compte = (int)$result['idcompte'];
        
        // Créer le profil professeur
        $sql = "INSERT INTO Professeur (idProfesseur, identifiantProf) 
                VALUES (:id, :identifiant)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id_compte,
            ':identifiant' => $identifiant
        ]);
        $stats['professeurs']++;
        
        return $id_compte;
    }
    
    private function getFirstResponsable(PDO $pdo): int
    {
        // Chercher d'abord christelle.roze
        $sql = "SELECT c.idCompte FROM Compte c 
                JOIN Responsable_Pedagogique rp ON c.idCompte = rp.idResponsablePedagogique 
                WHERE c.identifiantCompte = 'christelle.roze'
                LIMIT 1";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['idcompte'])) {
            return (int)$result['idcompte'];
        }
        
        // Si christelle.roze n'existe pas, prendre le premier responsable disponible
        $sql = "SELECT idResponsablePedagogique FROM Responsable_Pedagogique LIMIT 1";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['idresponsablepedagogique'])) {
            return (int)$result['idresponsablepedagogique'];
        }
        
        // Si aucun responsable n'existe, créer christelle.roze comme responsable par défaut
        $sql = "INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction) 
                VALUES ('christelle.roze', :password, 'ROZE', 'Christelle', 'responsable_pedagogique')
                ON CONFLICT (identifiantCompte) DO NOTHING
                RETURNING idCompte";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':password' => $this->generatePassword()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['idcompte'])) {
            $id_compte = (int)$result['idcompte'];
        } else {
            // Le compte existe déjà, récupérer son ID
            $sql = "SELECT idCompte FROM Compte WHERE identifiantCompte = 'christelle.roze'";
            $stmt = $pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_compte = (int)$result['idcompte'];
        }
        
        // Créer ou récupérer le profil responsable
        $sql = "INSERT INTO Responsable_Pedagogique (idResponsablePedagogique, identifiantRp) 
                VALUES (:id, 'christelle.roze')
                ON CONFLICT (idResponsablePedagogique) DO NOTHING";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_compte]);
        
        return $id_compte;
    }
    
    private function getOrCreateCours(PDO $pdo, int $id_ressource, int $id_professeur, 
                                      int $id_responsable, string $type, bool $evaluation,
                                      string $date_debut, string $date_fin, array &$stats): int
    {
        // Vérifier si le cours existe déjà (même ressource, prof, type et date)
        $sql = "SELECT idCours FROM Cours 
                WHERE idRessource = :ressource 
                AND idProfesseur = :professeur 
                AND type = :type 
                AND date_debut = :date_debut";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ressource' => $id_ressource,
            ':professeur' => $id_professeur,
            ':type' => $type,
            ':date_debut' => $date_debut
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && isset($result['idcours'])) {
            return (int)$result['idcours'];
        }
        
        // Créer le cours
        $sql = "INSERT INTO Cours (idRessource, idProfesseur, idResponsablePedagogique, 
                                   type, evaluation, date_debut, date_fin) 
                VALUES (:ressource, :professeur, :responsable, :type, :evaluation, :date_debut, :date_fin)
                RETURNING idCours";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ressource' => $id_ressource,
            ':professeur' => $id_professeur,
            ':responsable' => $id_responsable,
            ':type' => $type,
            ':evaluation' => $evaluation ? 1 : 0,
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['cours']++;
        
        return (int)$result['idcours'];
    }
    
    private function createAbsence(PDO $pdo, int $id_cours, int $id_etudiant, 
                                   string $date_debut, string $date_fin, 
                                   string $motif, ?bool $justifie, array &$stats): void
    {
        // Vérifier si l'absence existe déjà
        $sql = "SELECT idAbsence FROM Absence 
                WHERE idCours = :cours AND idEtudiant = :etudiant AND date_debut = :date_debut";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cours' => $id_cours,
            ':etudiant' => $id_etudiant,
            ':date_debut' => $date_debut
        ]);
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return; // L'absence existe déjà
        }
        
        $sql = "INSERT INTO Absence (idCours, idEtudiant, date_debut, date_fin, motif, justifie) 
                VALUES (:cours, :etudiant, :date_debut, :date_fin, :motif, :justifie)";
        $stmt = $pdo->prepare($sql);
        
        // Gérer null, true ou false pour justifie
        $justifieValue = null;
        if ($justifie !== null) {
            $justifieValue = $justifie ? 1 : 0;
        }
        
        $stmt->execute([
            ':cours' => $id_cours,
            ':etudiant' => $id_etudiant,
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin,
            ':motif' => $motif,
            ':justifie' => $justifieValue
        ]);
        $stats['absences']++;
    }

    private function convertToSeconds(string $str): int
    {
        return match($str) { // Retourne nombre de secondes correspondant
            '1H30' => 5400,
            '3H00' => 10800,
            default => 0
        };
    }

    private function getDate(string $date, string $heure): int
    {
        // Format date: "21/02/2024"
        $jour = intval(substr($date, 0, 2));
        $mois = intval(substr($date, 3, 2));
        $annee = intval(substr($date, 6, 4));

        // Format heure: "9H30" ou "11H00"
        $heure = str_replace('H', ':', $heure);
        $parts = explode(':', $heure);
        $heures = intval($parts[0]);
        $minutes = isset($parts[1]) ? intval($parts[1]) : 0;

        return mktime($heures, $minutes, 0, $mois, $jour, $annee);
    }


    public function filtrerEvals($files) {
        $csv = new GestionCSV();
        $result = [];
        $csv_array = $csv->getAllData($files);

        // Filtrer que les absences qui ont des évaluations
        foreach ($csv_array as $row) {
            if ($row[23] == "Oui") {
                $result += ($row);
            }
        }

        return $result;
    }


    public function filtrerNom($files, string $nom) {
        $csv = new GestionCSV();
        $result = [];
        $csv_array = $csv->getAllData("data/CSV/BUT1-240122-240223_anonymise.CSV");
        // Filtrer que les absences qui ont le nom d'étudiant spécifié
        foreach ($csv_array as $row) {
            if ($row[0] == $nom) {
                $result += ($row);
            }
        }
        return $result;
    }

    public function filtrerPrenom($files, string $prenom) {
        $csv = new GestionCSV();
        $result = [];
        $csv_array = $csv->getAllData($files);

        // Filtrer que les absences qui ont le prénom d'étudiant spécifié
        foreach ($csv_array as $row) {
            if ($row[1] == $prenom || $row[2] == $prenom ) {
                $result += ($row);
            }
        }
        return $result;
    }
}
