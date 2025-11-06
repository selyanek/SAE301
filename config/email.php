<?php
// Configuration SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'gestion.absences12@gmail.com');
define('SMTP_PASSWORD', 'zxxm srcb zvox vzgx');
define('SMTP_ENCRYPTION', 'tls');

// Informations de l'expéditeur
define('EMAIL_FROM', 'noreply@iut-uphf.fr');         // Email expéditeur
define('EMAIL_FROM_NAME', 'Gestion Absences IUT');   // Nom expéditeur

// Préfixe pour les sujets d'emails (facilite le filtrage)
define('EMAIL_SUBJECT_PREFIX', '[GESTION-ABS]');

// Activer/désactiver le mode debug
define('EMAIL_DEBUG', true);

// Configuration spécifique selon l'environnement
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    // Configuration pour développement local (MailHog, Mailtrap, etc.)
    define('SMTP_HOST_DEV', 'localhost');
    define('SMTP_PORT_DEV', 1025);
    define('USE_DEV_SMTP', true);
} else {
    define('USE_DEV_SMTP', false);
}
?>