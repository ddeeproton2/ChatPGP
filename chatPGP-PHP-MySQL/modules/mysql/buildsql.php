<?php


class buildsql{
/* // UTILISATION
	require_once('buildsql.php');
	$structure = buildsql::get_structure();
	// Modifie dans la table users la variable pass en type "pass"
	$structure["users"]["pass"] = "pass";
	$bsql = new buildsql($structure);
*/
	/*
	// Exemple de requete 
	$data = array(
		"table" => "",
		"data" => array(),
		"where" => array(),
		"nowhere" => array()
	);
	// Pour modifier une structure de table
	$structure = array(
		"tabname" => array(
			"varname" => "typeCast",
		)
	)
	*/
	public static $structure;
	public static $error;
	public static $logs;
	public $query_sql;

	function __construct($structure){
		self::$structure = $structure;
		self::$error = false;
		self::$logs = array();
	}

	
	public static function build_limit($count, $start = false) {
		if($start!=false && is_numeric($count) && is_numeric($start)){
			return " LIMIT ".$start.", ".$count;
		}elseif(is_numeric($count)){
			return " LIMIT ".$count;	
		}
	}
	
	public static function build_order($order) {
		if(!is_array($order) || count($order) == 0) return "";
		$result = '';
		foreach($order as $n => $o) {
			//if(!isset(self::$structure[$n])) return "";
			if($result!='') $result .= ", ";
			$result .= "`".$n."` ".$o;
		}
		return " ORDER BY ".$result;
	}
	

	public static function table_exists($tablename) {
		if(!is_array(self::$structure)) {
			return False;
		}
		return isset(self::$structure[$tablename]);
	}
		
	public static function increment($tablename, $column_increment, $where_name, $where_value) {
		if(self::table_exists($tablename)) {
			self::$error = 1;
			return;
		}
		if(!isset(self::$structure[$tablename][$column_increment])){
			self::$error = 1;
			return;
		}
		if(!isset(self::$structure[$tablename][$where_name])){
			self::$error = 1;
			return;
		}
		if(!is_numeric($where_value)){
			self::$error = 1;
			return;
		}
		$sql = "UPDATE `".$tablename."` 
			SET `".$column_increment."` = `".$column_increment."` + 1 
			WHERE `".$where_name."` = ".$where_value."";

		return mysql::query($sql);
		
	}
	public static function create_table($tablename, $id, $data) {
		if(self::table_exists($tablename)) {
			self::$error = 1;
			return;
		}
		if(!is_array($data)) {
			self::$error = 3;
			return;
		}
		$sql = "CREATE TABLE IF NOT EXISTS `".$tablename."` (
			`".$id."` int(255) NOT NULL AUTO_INCREMENT, \n";
		foreach($data as $i => $v){
			$sql .= "`".$i."` ".$v." NOT NULL, \n";
		}
		$sql .= "	PRIMARY KEY (`".$id."`),
			KEY `".$id."` (`".$id."`)
		) AUTO_INCREMENT=1 ;";
		//) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

		return mysql::query($sql);
		
	}
	
	public static function select($d, $showDebug = false) {
		global $query_sql;
		
		if(!isset($d['table']) || $d['table'] == '') return array("error"=>true,"errorMessage"=>'Erreur SELECT (pas de nom de table)');
		$required = self::check_required($d);
		if(isset($required["error"])) return $required;
		$select = isset($d['select']) ? $d['select']:'';
		if(!isset($d['limit']['start'])) $d['limit']['start'] = false;
		if(!isset($d['limit']['count'])) $d['limit']['count'] = false;
		if(!isset($d['order'])) $d['order'] = array();
		$sql = "SELECT ".self::build_select_varnames($select)
			." FROM `".$d['table']."` "
			.self::build_where($d)
			.self::build_limit($d['limit']['count'], $d['limit']['start'])
			.self::build_order($d['order']).";";
		self::$logs[] = $sql;
		if($showDebug) echo "<pre>".$sql."</pre>";
		return mysql::query($sql);
	}
	public static function insert($d) {
		if(!isset($d['table']) || $d['table'] == '') return array("error"=>true,"errorMessage"=>'Erreur INSERT (pas de nom de table)');
		if(!isset($d['data']) || !is_array($d['data'])) return array("error"=>true,"errorMessage"=>'Erreur INSERT (data est vide)');
		$required = self::check_required($d);
		if(isset($required["error"])) return $required;
		//die("INSERT INTO `".$d['table']."` ".self::build_values($d['table'], $d['data']).";");
		return mysql::query("INSERT INTO `".$d['table']."` ".self::build_values($d['table'], $d['data']).";");
	}
	public static function update($d) {
		if(!isset($d['table']) || $d['table'] == '') return array("error"=>true,"errorMessage"=>'Erreur UPDATE (pas de nom de table)');
		if(!isset($d['data']) || !is_array($d['data'])) return array("error"=>true,"errorMessage"=>'Erreur UPDATE (data est vide)');
		$required = self::check_required($d);
		if(isset($required["error"])) return $required;
		return mysql::query("UPDATE `".$d['table']."` SET ".self::build_equal($d['table'], $d['data'], false).self::build_where($d).";");
	}
	public static function delete($d) {
		if(!isset($d['table']) || $d['table'] == '')  return array("error"=>true,"errorMessage"=>'Erreur DELETE (pas de nom de table)');
		$where = self::build_where($d);
		if($where == '') return array("error"=>true,"errorMessage"=>'Erreur DELETE (where est vide)');
		$required = self::check_required($d);
		if(isset($required["error"])) return $required;
		 return mysql::query("DELETE FROM `".$d['table']."`".$where.";");
	}
	public static function get_structure() {
		$structure = array();
		$req = mysql::query("SHOW TABLES;");
		if(isset($req['error'])) {
			die($req['errorMessage']);
		}
		foreach($req as $table){
			foreach($table as $t){
				if(!isset($structure[$t])) $structure[$t] = array();
				foreach(mysql::query("DESCRIBE `".$t."`") as $d){
					$structure[$t][$d['Field']] = $d['Type'];
				}
			}
		}
		return $structure;
	}
	

	private static function check_required($d) {
		if(!isset($d['required'])) return;
		if(is_array($d['required'])) {
			foreach($d['required'] as $data_index => $required) {
				if(is_array($required)) {
					foreach($required as $r) {
						if(!isset(self::$structure[$d['table']][$r])) 
							return array("error" => true,"errorMessage" => 'Le champ n\'existe pas ['.$d['table'].']['.$r.'].');
						$type = self::$structure[$d['table']][$r];
						$protected = self::protect_data($type, $d[$data_index][$r]);
						if($protected == '' || $protected == "''") 
							return array("error" => true,"errorMessage" => 'Champ obligatoire vide ['.$d['table'].']['.$r.'] = '.$protected);
					}
				}else{
					foreach(self::$structure[$d['table']] as $required => $type) {
						if(!isset($d[$data_index][$required])) break;
						$protected = self::protect_data($type, $d[$data_index][$required]); 
						if($protected == '' || $protected == "''") 
							return array("error" => true,"errorMessage" => 'Champ obligatoire vide ['.$required.'].');
					}
				}
			}
		}elseif($d['required']) {
			foreach($d['data'] as $index => $value) {
				$type = self::$structure[$d['table']][$index];
				$protected = self::protect_data($type, $value); 
				if($protected == '' || $protected == "''")  
					return array(
						"error" => true,
						"errorMessage" => 'Champ obligatoire vide ['.$value.'].'
					);
			}
		}
		return array("error" => false);
	}
	private static function protect_onlynumeric($d) {
		return preg_replace('/\D/', '', $d);
	}
	private static function protect_only_numeric_and_chars($txt){
		return preg_replace("/[^ \w]+/", "", $txt);
	}
	private static function protect_only_datetime($txt){
		return preg_replace("/[^ \w:-]+/", "", $txt);
	}
	private static function protect_only_text($txt){
		//return mysql_real_escape_string($txt);
		return mysqli_real_escape_string(mysql::$bdd,$txt);
	}
	
	
	
	

	
	private static function build_values($tablename, $data) {
		if(!self::table_exists($tablename)) {
			self::$error = 1;
			return;
		}
		if(!is_array($data)) {
			self::$error = 2;
			return;
		}
		$names = "";
		$values = "";
		foreach($data as $n => $d) {
			if(!isset(self::$structure[$tablename][$n])) continue;
			if($names != '') $names .= ', ';
			if($values != '') $values .= ', ';
			$names .= '`'.self::protect_only_numeric_and_chars($n).'`';
			$type = self::$structure[$tablename][$n];
			$values .= self::protect_data($type, $d);
		}
		if($names!= '' && $values != '') {
			return " (".$names.") VALUES (".$values.")";
		}
	}
	public static function structure_type($type) {
		$pos = strpos($type, "(");
		if($pos > 0) $type= substr($type, 0, $pos);
		return $type;
	}
	private static function protect_data($type, $d) {
		$type = self::structure_type($type);
		if($type == 'int' || $type == 'bigint') {
			return self::protect_onlynumeric($d);
		}elseif($type == 'date' || $type == 'datetime') {
			return "'".self::protect_only_datetime($d)."'";
		}elseif($type == 'varchar' || $type == 'text') {
			return "'".self::protect_only_text($d)."'";
		}elseif($type == 'pass') {
			return "'".self::protect_only_numeric_and_chars($d)."'";
		}
	}

	private static function build_equal($tablename, $data, $isSeparatorAND) {
		return self::build($tablename, $data, true, $isSeparatorAND);
	}
	
	private static function build_noequal($tablename, $data, $isSeparatorAND) {
		return self::build($tablename, $data, false, $isSeparatorAND);
	}
	
	private static function build($tablename, $data, $isEqual, $isSeparatorAND) {
		if(!is_array(self::$structure)) {
			self::$error = 1;
			return;
		}
		if(!is_array($data)) {
			self::$error = 2;
			return;
		}

		if(!isset(self::$structure[$tablename])) {
			self::$error = 3;
			return;
		}
		
		//echo '<pre>';var_dump($data);echo '</pre>';
		
		$tester = $isEqual ? "=":"!=";
		$separator = $isSeparatorAND ? ' AND ' : ', ';
		$return = "";
		foreach($data as $n => $d) {
			if(!isset(self::$structure[$tablename][$n])) {
				echo '<pre>[ '.$tablename.' -> '.$n.' ] n\'existe pas.</pre>';
				continue;
			}
			if($return != '') $return .= $separator;
			if(is_array($d)) {
				$txt = '';
				foreach($d as $d2) {
					if($txt != '') $txt .= ' OR ';
					$txt .= '`'.self::protect_only_numeric_and_chars($n).'` '.$tester.' '.self::protect_data(self::$structure[$tablename][$n], $d2);
				}
				$return .= '('.$txt.')';
			}else{
				//if(!isset(self::$structure[$tablename][$n])) continue;
				
				$return .= '`'.self::protect_only_numeric_and_chars($n).'` '.$tester.' ';
				$type = self::$structure[$tablename][$n];
				$return .= self::protect_data($type, $d);
			}
		}
		return " ".$return;
	}
	private static function build_where($d) {
		$where = "";
		if(isset($d['where']) && is_array($d['where'])) $where .= self::build_equal($d['table'], $d['where'], true);
		if(isset($d['orwhere']) && is_array($d['orwhere'])) {
			$orwhere = self::build_equal($d['table'], $d['orwhere'], true);
			if($where != '' && $orwhere != '') {
				$where .= ' OR '.$orwhere;
			}
		}
		if(isset($d['nowhere']) && is_array($d['nowhere'])) {
			$nowhere = self::build_noequal($d['table'], $d['nowhere'], true);
			if($where != '' && $nowhere != '') {
				$where .= ' AND '.$nowhere;
			}
		}
		if(isset($d['like']) && is_array($d['like'])) {
			$like = self::build_like($d, true);
			if($where != '' && $like != '') {
				$where .= ' AND ('.$like.')';
			}
		}
		if($where != '') return " WHERE ".$where;
	}
	private static function build_like($data, $isSeparatorAND) {
		if(!is_array($data)) return;
		if(!isset($data['table'])) return;
		$return = "";
		$separator = $isSeparatorAND ? ' OR ' : ', ';
		foreach($data['like'] as $n => $d) {
			if(!isset(self::$structure[$data['table']][$n])) continue;
			$protected = self::protect_only_text($d);
			if($protected == '') continue;
			if($return != '') $return .= $separator;
			$return .= 'LOWER(`'.self::protect_only_numeric_and_chars($n)."`) LIKE LOWER('%".$protected."%')";
		}
		return " ".$return;

	}

	private static function build_select_varnames($data = array()) {
		if(!is_array($data)) {
			if($data == ''){
				return '*';
			}else{
				return $data;
			}
		}
		$return = '';
		foreach($data as $d){
			if($return != '') $return .= ','; 
			$return .= '`'.self::protect_only_numeric_and_chars($d).'`';
		}
		if($return == '') return '*';
		return $return;
	}
	public static function req_to_array($req, $index, $value, $array_before = array()) {
		if(!is_array($req)) return array();
		$return = array();
		foreach($array_before as $i=>$v) {
			$return[$i] = $v;
		}
		foreach($req as $d) {
			$return[$d[$index]] = $d[$value];
		}
		return $return;
	}
}
?>