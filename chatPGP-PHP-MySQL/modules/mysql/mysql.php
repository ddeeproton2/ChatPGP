<?php 

class mysql
{ 

    public static $bdd;

    function __construct($structure){
            self::$bdd;
    }

    public static function connect($addrServeur, $utilisateur, $motspasse, $baseDeDonnee) {
        self::$bdd = new mysqli($addrServeur, $utilisateur, $motspasse, $baseDeDonnee)
        or die('Erreure de connexion à la base SQL');
        if(self::$bdd->connect_error != ""){
            var_dump(array("host"=>$addrServeur, "user"=>$utilisateur,"pass"=>$motspasse,"db"=>$baseDeDonnee));
            die(self::$bdd->connect_error);
        }
        // Permet de ne plus afficher l'erreur suivante sur PHP 8.0:
        //Fatal error: Uncaught mysqli_sql_exception
        mysqli_report(MYSQLI_REPORT_STRICT);
    }

    public static function query($requete) {
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
    
    public static function protect($data) {
        return self::$bdd->real_escape_string($data);
    }
    
    public static function SQLDeconnecte() {
        self::$bdd->close();
    }
} 

if(!isset($config)){
    die("config.php n'est pas inclus");
}
//mysql::connect('gwm.myd.infomaniak.com','gwm_wp','5HTktaWDmOfQ','gwm_illicotravelch');
mysql::connect($config->sql_host, $config->sql_user, $config->sql_pass, $config->sql_base);
mysql::$bdd->set_charset('utf8mb4');
session_start();
