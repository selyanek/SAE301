<?php require __DIR__ . '/../layout/header.php'; ?>

<link rel="stylesheet" href="/public/asset/CSS/cssConnexion.css">

<body>
    <section class="text-with-image-section">
        <div class="text-with-image">
            <img src="/public/asset/img/logoco.png" alt="Connexion">
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
            <input type="text" 
                   id="identifiant" 
                   name="identifiant" 
                   value="<?= htmlspecialchars($_POST['identifiant'] ?? '') ?>"
                   required 
                   autofocus>
            <br>
            
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" 
                   id="mot_de_passe" 
                   name="mot_de_passe" 
                   required>
            <br>
            
            <button type="submit">Se connecter</button>
            <br>
            
            <a href="mdpOublier.php">Mot de passe oublié ?</a>
        </form>
    </div>
</body>
</html>
