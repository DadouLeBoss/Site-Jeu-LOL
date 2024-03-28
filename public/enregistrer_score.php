<?php
// Connexion à la base de données
$connexion = new mysqli("localhost", "utilisateur", "motdepasse", "nom_de_la_base_de_donnees");

// Vérification de la connexion
if ($connexion->connect_error) {
    die("La connexion à la base de données a échoué : " . $connexion->connect_error);
}

// Récupération des données envoyées par la requête AJAX
$username = $_POST['username'];
$score = $_POST['score'];

// Préparation de la requête SQL pour insérer les données dans la table scores
$sql = "INSERT INTO scores (username, score) VALUES ('$username', $score)";

// Exécution de la requête SQL
if ($connexion->query($sql) === TRUE) {
    echo "Score enregistré avec succès !";
} else {
    echo "Erreur lors de l'enregistrement du score : " . $connexion->error;
}

// Fermeture de la connexion à la base de données
$connexion->close();
?>