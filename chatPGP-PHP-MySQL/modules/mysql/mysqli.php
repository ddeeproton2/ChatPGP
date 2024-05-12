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
	try{
		self::$bdd = new mysqli($addrServeur, $utilisateur, $motspasse, $baseDeDonnee)
		// Si erreure alors l'afficher et tout arrêter 
		or die("Erreure SQL : " . mysql_error()); 
	}catch(Exception $ex){
		die("Erreure SQL : $addrServeur, $utilisateur, $motspasse, $baseDeDonnee");
	}
  }


// -----------------------------------------------------------------------------
//  Fonction: Envoie une requête MySQL et retourne la réponse 
//    @paramètres:
//      $requete :    Requete soumise au serveur MySQL
//    @retourne:  	  Retourne la réponse du serveur MySQL
//------------------------------------------------------------------------------
  public static function query($requete) {
	  
	/*
    // Envoie la requête MySQL au serveur
    if(!($reponse = self::$bdd->query($requete))) {
		// Si erreure alors l'afficher et tout arrêter 
		return array(
				'error' => true,
				'errorMessage' => 'Requête invalide : '.mysql_error()."\nRequete SQL:\n".$requete.' ['.$reponse.']'
			);
	}

	$result = array();

	if($reponse) {
		if (method_exists($reponse,'fetch_assoc')) {
				while($row = $reponse->fetch_assoc()) {
					$result[] = $row;
				}
		}
	}

	// Retourne la réponse du serveur
    return $result;
	*/

	if(!($reponse = self::$bdd->query($requete))) {
		return array(
			'error' => true,
			'errorMessage' => 'Requête invalide = '.$requete,
			'sql' => $requete
		);
	}
	$result = array();
	if($reponse && $reponse !== true) {           
		while($row = $reponse->fetch_assoc()) {
			$result[] = $row;
		}
	}
	return $result;

  }


// -----------------------------------------------------------------------------
//  Fonction: Deconnecte PHP du serveur MySQL
//    @paramètre:   Aucun
//    @retourne:    Rien
//------------------------------------------------------------------------------  
  public static function SQLDeconnecte() {
    // Ferme la connexion au serveur MySQL
	self::$bdd->close();
  }
} 

?>