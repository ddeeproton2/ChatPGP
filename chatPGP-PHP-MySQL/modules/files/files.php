<?php





class files {


// -----------------------------------------------------------------------------
//  Fonction: Retourne dans un tableau la liste de fichiers contenus dans un dossier 
//    @parametres:
//      $dir : Chemin du dossier
//      $type : onlyfile | onlydir | all
//      $type : 
//			GLOB_MARK : Ajoute un slash final à chaque dossier retourné
//			GLOB_NOSORT : Retourne les fichiers dans l'ordre d'apparence (pas de tri)
//			GLOB_NOCHECK : Retourne le masque de recherche si aucun fichier n'a été trouvé
//			GLOB_NOESCAPE : Ne protège aucun métacaractère d'un antislash
//			GLOB_BRACE : Remplace {a,b,c} par 'a', 'b' ou 'c'
//			GLOB_ONLYDIR : Ne retourne que les dossiers qui vérifient le masque
//			GLOB_ERR : Stop lors d'une erreur (comme des dossiers non lisibles), par défaut, les erreurs sont ignorées.

//    @retourne:  tableau de fichiers
//------------------------------------------------------------------------------

    public static function listFilesInDir($dir, $type = GLOB_NOSORT){
            if(is_dir($dir)) $dir .='/*';
            $files = glob($dir, $type);
            return is_array($files) ? $files : array();
    }

    //use
    //files::listFilesInDir('/images/'); 
    //files::listFilesInDir('/images/*.[jJ][pP][gG]'); 

// -----------------------------------------------------------------------------
//  Fonction: Retourne l'extention d'un fichier
//    @parametres:
//      $dir : Nom du fichier
//    @retourne:  l'extention sans le point "."
//------------------------------------------------------------------------------

    public static function getFileExtention($file){
        $t = array();
        $t = explode(".", $file);
        if(count($t)>0) {
            return $t[count($t)-1];
        }else{
            return "";
        }
    }

// -----------------------------------------------------------------------------
//  Fonction: Retourne le nom d'un fichier
//    @parametres:
//      $dir : Nom du fichier
//    @retourne:  l'extention sans le point "."
//------------------------------------------------------------------------------

    public static function filename($file){
        $t = array();
        if(strpos($file, "/") !== false) {
            $exp = "/";
        }elseif(strpos($file, "\\") !== false) {
            $exp = "\\";
        }else{
            return $file;
        }
        $t = explode($exp, $file);
        if(count($t)>0) {
            return $t[count($t)-1];
        }else{
            return "";
        }
    }



    public static function getParentPath($dir) {
        $dirname = files::filename($dir);
        return substr($dir,0,strlen($dir)-strlen($dirname));
    }

	
// -----------------------------------------------------------------------------
//  Fonction: Créer un dossier avec les permissions d'écriture
//    @parametres:
//      $dir : Chemin du dossier
//    @retourne:  rien
//------------------------------------------------------------------------------

    public static function createDir($dir){
        if(is_dir($dir)) return;
        $oldumask = umask(0000);
        mkdir($dir);
        umask($oldumask);
    }

    public static function copyDir($source, $dest){
        global $lasterror;
        if (is_file($source)) {
            return copy($source, $dest);
        }
        if (!is_dir($dest)) {
                mkdir($dest);
        }
        $success = true;
        $lasterror = "";
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            if($success) {
                $success = files::copyDir("$source/$entry", "$dest/$entry");
                if(!$success) $lasterror .= "Error on copy file:\n\t$source/$entry\n\t=> $dest/$entry";
            }else{
                files::copyDir("$source/$entry", "$dest/$entry");
            }
        }
        $dir->close();
        return $success;
    }



// -----------------------------------------------------------------------------
//  Fonction: Lit le contenu d'un fichier
//    @parametres:
//      $file : Chemin du fichier 
//    @retourne:  Le contenu du fichier
//------------------------------------------------------------------------------

    public static function read($file){
        if(is_file($file) && filesize($file) > 0) {
            $fp = fopen($file, "rb");
            $txt = fread($fp, filesize($file));
            fclose($fp);
            return $txt;
        }
    }


// -----------------------------------------------------------------------------
//  Fonction: Ecrire dans un fichier
//    @parametres:
//      $file : Chemin du fichier
//		$txt : Contenu du fichier 
//    @retourne:  rien
//------------------------------------------------------------------------------

 	

    public static function writeAppend($file, $txt){
        $fp = fopen($file,"a");                 
        fputs($fp, $txt);            
        fclose($fp);
    }

 	

    public static function write($file, $txt){
        $fp = fopen($file,"w+");                 
        fputs($fp, $txt);            
        fclose($fp);
    }


    public static function readFast($file){
        readfile($file);
    }	

    public static function readFastToString($file){
        return is_file($file) ? file_get_contents($file) : '';
    }	

    public static function writeFast($file, $txt){
        file_put_contents($file, $txt);
    }	

	

// -----------------------------------------------------------------------------

// @param int $bytes Number of bytes (eg. 25907)

// @param int $precision [optional] Number of digits after the decimal point (eg. 1)

// @return string Value converted with unit (eg. 25.3KB)

// -----------------------------------------------------------------------------



    public static function convertSizeformat($bytes, $precision = 2) {
        $unit = array("o", "Ko", "Mo", "Go");
        $exp = floor(log($bytes, 1024)) | 0;
        return round($bytes / (pow(1024, $exp)), $precision).' '.$unit[$exp];
    }

	

}



?>