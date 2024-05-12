<?php

class mysql
{ 

	public static $bdd;
	
	function __construct($structure){
		self::$bdd;
	}
// -----------------------------------------------------------------------------
//  Fonction: Connexion au serveur MySQL et sélection de la base de donnée 
//    @paramètres:
//      $addrServeur  :    Adresse du serveur MySQL
//      $utilisateur  :    Nom d'utilisateur MySQL
//      $motspasse    :    Mots de passe utilisateur MySQL
//      $baseDeDonnee :    Nom de la base de donnée à sélectionner
//    @retourne:  		   Rien
//------------------------------------------------------------------------------
  public static function connect($addrServeur, $utilisateur, $motspasse, $baseDeDonnee) {
    // Connexion à la base de donnée.
    $lienSQL = mysql_connect($addrServeur, $utilisateur, $motspasse)
	// Si erreure alors l'afficher et tout arrêter 
    or die("Erreure SQL : " . mysql_error()); 

    // Sélection de la base de donnée
    mysql_select_db($baseDeDonnee, $lienSQL)
	// Si erreure alors l'afficher et tout arrêter 	
    or die ('Erreure SQL : ' . mysql_error().var_dump(mysql_fetch_assoc(mysql_query("SHOW DATABASES;"))));

  }	


// -----------------------------------------------------------------------------
//  Fonction: Envoie une requête MySQL et retourne la réponse 
//    @paramètres:
//      $requete :    Requete soumise au serveur MySQL
//    @retourne:  	  Retourne la réponse du serveur MySQL
//------------------------------------------------------------------------------
  public static function query($requete) {
    // Envoie la requête MySQL au serveur
    if(!($reponse = mysql_query($requete))) {
		return array(
				'error' => true,
				'errorMessage' => 'Requête invalide : '.mysql_error()."\nRequete SQL:\n".$requete.' ['.$reponse.']'
			);
	}
	// Si erreure alors l'afficher et tout arrêter 
	/*
	or return array(
		'error' => true,
		'errorMessage' => 'Requête invalide : '.mysql_error().' '.$requete.' ['.$reponse.']'
	); */
	$result = array();
	//if(mysql_num_rows($reponse)!== false) {
	if($reponse) {
		while($row = @mysql_fetch_assoc($reponse)) {
			$result[] = $row;
		}
	}
	// Retourne la réponse du serveur
    return $result;
  }


// -----------------------------------------------------------------------------
//  Fonction: Deconnecte PHP du serveur MySQL
//    @paramètre:   Aucun
//    @retourne:    Rien
//------------------------------------------------------------------------------  
  public static function SQLDeconnecte() {
    // Ferme la connexion au serveur MySQL
    mysql_close();
  }
} 

?>