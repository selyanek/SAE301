<?php
require __DIR__ . '/../vendor/autoload.php';
use src\Database\Database;
use src\Models\Login;

$message = "";

// Vérifier si l'utilisateur a été déconnecté pour inactivité
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $message = "Vous avez été déconnecté pour inactivité. Veuillez vous reconnecter.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = $_POST['identifiant'] ?? '';
    $mot_de_passe = ($_POST['mot_de_passe'] ?? '');
    $login = new Login($identifiant, $mot_de_passe);
    try {
        $bd = new Database();
        $pdo = $bd->getConnection();

        if ($login->verifierConnexion($pdo)) {
            session_start();
            $idSession = session_id();
            $_SESSION['login'] = $identifiant;
            $_SESSION['nom'] = $login->getName($pdo);
            $_SESSION["mdp"] = $login->getPwd($pdo);
            $_SESSION["role"] = $login->getRole($pdo);
            $_SESSION['last_activity'] = time(); // Initialiser le timestamp d'activité

            // Récupérer l'idCompte de l'utilisateur
            $user = $login->getIdUtilisateur($pdo);
            
            if ($user) {
                $_SESSION['idCompte'] = $user['idcompte'];
                $_SESSION['idEtudiant'] = $user['idcompte']; // Pour les étudiants, idEtudiant = idCompte
            }

            $role = $login->verifRole($pdo);
            if ($role == 'etudiant' || $role == 'etudiante') {
                header('Location: ../src/Views/etudiant/dashbord.php');
                exit();
            } elseif ($role == 'professeur') {
                header('Location: ../src/Views/accueil_prof.php');
                exit();
            } elseif ($role == 'responsable_pedagogique') {
                header('Location: ../src/Views/accueil_responsable.php');
                exit();
            } else {
                echo 'vous n exister pas';
            }
        } else {
            $message = "Identifiant ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        $message = "Erreur de connexion : " . $e->getMessage();
    }
}
require '../src/Views/layout/header.php';
?>
<link rel="stylesheet" href="/public/asset/CSS/cssConnexion.css">
<body>
<section class="text-with-image-section">
    <div class="text-with-image">
        <img src="../../public/asset/img/logoco.png" alt="Connexion">
        <h2>Connexion</h2>
    </div>
</section>
<div class="sidebar"></div>
<div class="wapper">
    <?php if (!empty($message)): ?>
        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <form action="index.php" method="post">
        <label for="identifiant">Identifiant :</label>
        <input type="text" id="identifiant" name="identifiant" required>
        <br>
        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        <br>
        <button type="submit">Se connecter</button>
        <br>
        <a href="mdpOublier.php">Mot de passe oublié ?</a>
    </form>
</div>
</body>
<?php
require '../src/Views/layout/footer.php';
?>
</html>