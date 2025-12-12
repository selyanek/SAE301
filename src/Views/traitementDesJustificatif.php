<?php
session_start();
require __DIR__ . '/../Controllers/session_timeout.php'; // Gestion du timeout de session
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/Absence.php';

// Instancier les classes sans l'usage de "use" pour garder la compatibilité dans le template
$db = new \src\Database\Database();
$pdo = $db->getConnection();
$absenceModel = new \src\Models\Absence($pdo);

// ID reçu depuis gestionAbsResp.php
$id = isset($_GET['id']) ? intval($_GET['id']) : -1;

// Absence correspondante depuis la DB
$absence = ($id > 0) ? $absenceModel->getById($id) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Traitement des justificatifs</title>
    <link href="/public/asset/CSS/cssTraitementDesJustificatifs.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssDeBase.css" rel="stylesheet">
    <link href="/public/asset/CSS/cssGestionAbsResp.css" rel="stylesheet">
</head>
<body>

<div class="uphf">
    <img src="../../public/asset/img/logouphf.png" alt="Logo uphf">
</div>
<div class="logoEdu">
    <img src="../../public/asset/img/logoedutrack.png" alt="Logo EduTrack">
</div>

<div class="sidebar">
    <ul>
        <li><a href="accueil_responsable.php">Accueil</a></li>
        <li><a href="/src/Controllers/profile.php">Mon profil</a></li>
        <li><a href="historiqueAbsResp.php">Historique des absences</a></li>
        <!-- <li><a href="#">Statistiques</a></li> -->
    </ul>
</div>

<header class="titre">
    <h1>Détails du justificatif des absences</h1>
</header>

<div class="trait"></div>

<?php if (!$absence): ?>
    <div class="error-message">Absence introuvable.</div>
    <div class="buttons">
        <a href="gestionAbsResp.php"><button type="button" class="btn">Retour</button></a>
    </div>

<?php else: ?>

    <div class="encadre1">
        <div class="encadre2">
            <h3>Date de début de l'absence</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))); ?></p>
            </div>
        </div>

        <div class="encadre2">
            <h3>Date de fin de l'absence</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_fin']))); ?></p>
            </div>
        </div>
    </div>

    <div class="encadre1">
        <div class="encadre2">
            <h3>Date de soumission</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($absence['date_debut']))); ?></p>
            </div>
        </div>

        <div class="encadre2">
            <h3>Motif</h3>
            <div class="encadre3">
                <p><?php echo htmlspecialchars($absence['motif'] ?? '—'); ?></p>
            </div>
        </div>
    </div>

    <div class="trait"></div>

    <form action="../Controllers/traiter_absence.php" method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

        <div class="encadre1">
            <div class="encadre2">
                <h3>Document(s) justificatif(s)</h3>
                <div class="encadre3">
                    <?php
                    if (!empty($absence['urijustificatif'])) {
                        $fichiers = json_decode($absence['urijustificatif'], true);
                        if (is_array($fichiers) && count($fichiers) > 0) {
                            foreach ($fichiers as $index => $fichier) {
                                $fichierPath = "../../uploads/" . htmlspecialchars($fichier);
                                echo "<p><a href='" . $fichierPath . "' target='_blank'>" . htmlspecialchars($fichier) . "</a></p>";
                            }
                        } else {
                            echo "<p>—</p>";
                        }
                    } else {
                        echo "<p>Aucun document fourni</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="encadre2">
                <h3>Cours</h3>
                    <div class="encadre3">
                        <p><?php echo htmlspecialchars($absence['cours_type'] ?? '—'); ?></p>
                        <p><?php echo htmlspecialchars($absence['ressource_nom'] ?? '—'); ?></p>
                    </div>
            </div>
        </div>

        <div class="boutons">
            <button type="submit" name="action" value="valider">Valider</button>
            <button type="button" onclick="afficherRaisonRefus()">Refuser</button>
            <button type="submit" name="action" value="Demande_justif">Demander justificatif</button>
            <a href="gestionAbsResp.php"><button type="button">Retour</button></a>
        </div>
        
        <!-- Zone pour saisir la raison du refus (cachée par défaut) -->
        <div id="zone-raison-refus">
            <h3>Raison du refus</h3>
            <p>Indiquer la raison du refus :</p>
            <textarea name="raison_refus" id="raison_refus" rows="4" 
                      placeholder="Ex: usage de faux, document(s) illisible(s) ..."></textarea>
            <div class="btn-actions">
                <button type="submit" name="action" value="refuser" class="btn-confirmer-refus">Confirmer le refus</button>
                <button type="button" onclick="cacherRaisonRefus()" class="btn-annuler-refus">Annuler</button>
            </div>
        </div>
    </form>

    <script>
        function afficherRaisonRefus() {
            document.getElementById('zone-raison-refus').style.display = 'block';
            document.getElementById('raison_refus').focus();
        }
        
        function cacherRaisonRefus() {
            document.getElementById('zone-raison-refus').style.display = 'none';
            document.getElementById('raison_refus').value = '';
        }
        
        // Validation côté client pour s'assurer qu'une raison est fournie lors du refus
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const action = e.submitter ? e.submitter.value : null;
                    const raisonRefus = document.getElementById('raison_refus');
                    
                    if (action === 'refuser' && raisonRefus && raisonRefus.value.trim() === '') {
                        e.preventDefault();
                        alert('Veuillez indiquer une raison pour le refus.');
                        raisonRefus.focus();
                        return false;
                    }
                });
            }
        });
    </script>

<?php endif; ?>

<!-- Formulaire de demande de justificatifs -->
<?php if (isset($_GET['demande']) && $_GET['demande'] === 'true'): ?>
    <div class="zone-demande-justif">
        <h3>Envoyer une demande de justificatifs à l'étudiant</h3>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="message-erreur">
                <strong>Erreur :</strong>
                <?php 
                switch($_GET['error']) {
                    case 'champ_vide':
                        echo 'Veuillez remplir le motif de la demande.';
                        break;
                    case 'email_invalide':
                        echo 'L\'adresse email de l\'étudiant est invalide.';
                        break;
                    case 'identifiant_manquant':
                        echo 'L\'identifiant de l\'étudiant est manquant.';
                        break;
                    case 'envoi_echoue':
                        echo 'L\'envoi de l\'email a échoué. Veuillez réessayer.';
                        break;
                    default:
                        echo 'Une erreur s\'est produite.';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <p class="info-etudiant">
            <strong>Étudiant :</strong> <?php echo htmlspecialchars(($absence['prenomcompte'] ?? '') . ' ' . ($absence['nomcompte'] ?? '')); ?><br>
            <strong>Email :</strong> <?php 
                $identifiant = $absence['identifiantcompte'] ?? '';
                $emailEtudiant = (strpos($identifiant, '@') !== false) ? $identifiant : $identifiant . '@uphf.fr';
                echo htmlspecialchars($emailEtudiant); 
            ?>
        </p>
        
        <form action="../Controllers/traiter_absence.php" method="post">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
            <input type="hidden" name="action" value="envoyer_demande_justif">
            
            <div class="form-group">
                <label for="motif_demande">
                    Motif de la demande : <span class="obligatoire">*</span>
                </label>
                <p class="aide-texte">
                    Indiquez à l'étudiant la raison pour laquelle vous demandez des justificatifs supplémentaires.
                </p>
                <textarea 
                    id="motif_demande" 
                    name="motif_demande" 
                    rows="6" 
                    required
                    placeholder="Exemple : Le certificat médical fourni ne couvre pas toute la période d'absence. Veuillez fournir un justificatif complet pour les dates manquantes du 10/12/2025 au 12/12/2025."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-envoyer">
                    Envoyer la demande
                </button>
                <a href="traitementDesJustificatif.php?id=<?php echo htmlspecialchars($id); ?>" class="btn-annuler">
                    Annuler
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- Message de confirmation d'envoi -->
<?php if (isset($_GET['email_sent']) && $_GET['email_sent'] === 'true'): ?>
    <div class="message-succes">
        <h3>✓ Email envoyé avec succès</h3>
        <p>
            La demande de justificatifs supplémentaires a été envoyée à <strong><?php echo htmlspecialchars(($absence['prenomcompte'] ?? '') . ' ' . ($absence['nomcompte'] ?? '')); ?></strong>.
        </p>
        <div class="actions">
            <a href="gestionAbsResp.php" class="btn-retour">
                Retour à la liste des absences
            </a>
        </div>
    </div>
<?php endif; ?>

<div class="espacement-footer"></div>

<footer class="footer">
    <nav class="footer-nav">
        <a href="accueil_responsable.php">Accueil</a>
        <span>|</span>
        <a href="">Aides</a>
    </nav>
</footer>
</body>
</html>
