<?php
require_once('modules/mysql/mysqli.php');
require_once('modules/mysql/buildsql.php');

define("SERVER_TIMEOUT", 30); // 30 seconds
define("CHAT_TIMEOUT", 40); // 40 seconds

$config = array(
	'mysql_address' => '127.0.0.1',
	'mysql_username' => 'root',
	'mysql_password' => '',
	'mysql_database' => 'tchat1'
);

Request::allowGetMethod();
Request::controler();

// ================ CLASS ================
class Action {

	public static function get($params = array()){
		global $sql;
		$login = Chat::login($params);
		if($login == False || !is_array($login) || count($login) < 2) {
			die(json_encode(array("Error"=>"login fail")));
		}
		list($user, $usession) = $login;
		$timestamp = Request::waitForChanges($user);
		list($u, $usession) = Chat::currentUser(array('usersession'=>$usession));
		if($u != False) $user = $u;
		Chat::send_data($params, $timestamp, $user);
	}
	
	public static function setProfile($params = array()){
		list($user, $usession) = Chat::login($params);
		Chat::update_user($user, $params);
		list($user, $usession) = Chat::currentUser($params);
		Chat::send_data($params, '', $user);
	}
	
	public static function disconnect($params = array()){
		global $sql;
		list($user, $usession) = Chat::login($params);
		$sql->delete(array("table"=>'tchat_connected', "where"=>array("id_user"=>$user['id_user'])));
		Chat::onUserDisconnected();
	}
}

class Request {
	public static function controler(){
		if(isset($_POST['action'])
		&& method_exists(new Action,$_POST['action'])) {
			Request::SQLConnexion();
			if(!isset($_POST['params']) || !is_array($_POST['params'])) { 
				$_POST['params'] = array();
			}
			Action::$_POST['action']($_POST['params']);
		}		
	}
	public static function allowGetMethod(){
		foreach($_GET as $i => $v){
			$_POST[$i] = $v;
		}	
	}
	public static function waitForChanges($user){
		if(!isset($user['id_user'])) return "";
		Chat::setUserConnected($user['id_user']);
		set_time_limit(0);
		$now =  strtotime(date('Y-m-d H:i:s'));
		$user_timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : '';
		$noChange = true;
		while($noChange) {
			if(Request::isRequestTimeout($now)) break;
			$timestamp = Request::getTimestamp($user['id_user']);
			$noChange = $timestamp == $user_timestamp;
			if($noChange) sleep(5);
		}
		return $timestamp;
	}
	
	public static function isRequestTimeout($now){
		return strtotime(date('Y-m-d H:i:s')) - $now > SERVER_TIMEOUT;
	}
	
	public static function setTimestamp($table, $id_user){
		global $sql;
		$req = $sql->select(array("table"=>'tchat_timestamp', "select"=>"*", 'where'=>array("name"=>$table,"id_user"=>$id_user)));
		$newhash = time();
		if(count($req)!=0) {
			$sql->update(array("table"=>'tchat_timestamp', 'data'=>array("hash"=>$newhash), 'where'=>array("name"=>$table,"id_user"=>$id_user)));
		}else{
			$req2 = $sql->insert(array("table"=>'tchat_timestamp', 'data'=>array("id_user"=>$id_user, "name"=>$table,"hash"=>$newhash)));
		}
	}
	
	public static function getTimestamp($id_user){
		global $sql;
		$bindTables = array(
			'tchat_messages',
			'tchat_connected',
			'tchat_rooms',
			'tchat_users'
		);
		$req = $sql->select(array("table"=>'tchat_timestamp', "select"=>"*", 'where'=>array("id_user"=>$id_user)));
		$hash = array();
		foreach($req as $r){
			if(isset($r['name']))
				$hash[$r['name']] = $r['hash'];
		}
		$timestamp = "";
		foreach($bindTables as $name){
			if(!isset($hash[$name])) $hash[$name] = -1;
			$timestamp .= $hash[$name].";";
		}
		return $timestamp;
	}
	
	public static function countTable($table){
		global $sql;
		$req = $sql->select(array("table"=>$table, "select"=>"count(*)"));
		return is_array($req) && count($req) != 0 && isset($req[0]['count(*)']) ? $req[0]['count(*)'] : 0;
	}
	
	public static function countTableWhere($table, $where){
		global $sql;
		$req = $sql->select(array("table"=>$table, "select"=>"count(*)", "where" => $where));
		return is_array($req) && count($req) != 0 && isset($req[0]['count(*)']) ? $req[0]['count(*)'] : 0;
	}
	
		
	public static function nextIDFromTable($table){
		$req = mysql::query("SHOW TABLE STATUS LIKE '$table'");
		return is_array($req) && count($req) != 0 && isset($req[0]['Auto_increment']) ? $req[0]['Auto_increment'] : False;
	}
	public static function SQLConnexion(){
		global $sql, $config;
		// SQL 
		mysql::connect($config['mysql_address'], $config['mysql_username'], $config['mysql_password'], $config['mysql_database']);
		$structure = buildsql::get_structure();
		//$structure["users"]["pass"] = "pass";
		$sql = new buildsql($structure);
		//$form = new buildform($structure);

		$sql->create_table('tchat_users', 'id_user', array(
				"user_enabled" => "int(11)",
				"user_name" => "varchar(255)",
				"user_pass" => "varchar(255)",
				"user_email" => "varchar(255)"
			)
		);
		$sql->create_table('tchat_sessions', 'id_session', array(
				"id_user" => "int(11)",
				"user_session" => "varchar(255)",
			)
		);
		$sql->create_table('tchat_rooms', 'id_room', array(
				"room_name" => "varchar(255)",
				"room_owner" => "int(11)",
				"room_private" => "int(11)"
			)
		);
		$sql->create_table('tchat_roomsusers', 'id_connected', array(
				"id_room" => "int(11)",
				"id_user" => "int(11)"
			)
		);
		$sql->create_table('tchat_connected', 'id_connected', array(
				"id_user" => "int(11)",
				"last_query" => "int(11)"
			)
		);
		$sql->create_table('tchat_messages', 'id_message', array(
				"id_room" => "int(11)",
				"id_user" => "int(11)",
				"message" => "text"
			)
		);
		$sql->create_table('tchat_timestamp', 'id_timestamp', array(
				"name" => "varchar(255)",
				"id_user" => "int(11)",
				"hash" => "varchar(255)"
			)
		);

	}
}

class Chat {
	public static function login($params = array(), $create_user = true){
		$curUser = Chat::currentUser($params);
		list($user, $usession) = $curUser;
		if($user != False) return $curUser;
		if($create_user) return Chat::create_newuser($params);
		return False;

	}
	
	public static function userExists($params = array()){
		global $sql;
		if(!isset($params['usersession']) || $params['usersession'] == '') return False;
		$req = $sql->select(array("table"=>'tchat_sessions', "select"=>"count(*)", 'where'=>array("user_session"=>$params['usersession'])));
		return is_array($req) && count($req) != 0 && isset($req[0]['count(*)']) ? $req[0]['count(*)'] != 0 : False;
	}
	
	public static function currentUser($params = array()){
		global $sql;
		if(!isset($params['usersession'])) return False;
		$req = $sql->select(array("table"=>'tchat_sessions', "select"=>"*", 'where'=>array("user_session"=>$params['usersession'])));
		if(!isset($req[0]) || !isset($req[0]['id_user'])) return False;
		$req = $sql->select(array("table"=>'tchat_users', "select"=>"*", 'where'=>array("id_user"=>$req[0]['id_user'])));
		if(is_array($req) && count($req) != 0 && isset($req[0]['id_user'])){
			$req[0]['user_session'] = $params['usersession'];
			return array($req[0], $params['usersession']);
		}
		return False;
	}
	
	public static function create_newuser($params = array()){
		global $sql;
		$nextid = Request::nextIDFromTable('tchat_users');
		if(!is_numeric($nextid)) die('Error: create_newuser() ($nextid is not numeric');
		$sql->insert(array("table"=>'tchat_users', 'data'=>array("user_name"=>"guest_".$nextid,"user_enabled"=>1,"user_pass"=>"")));
		$id = mysqli_insert_id(mysql::$bdd);
		do {
			$newhash = md5($id.time().rand(0,1000));
			$req = $sql->select(array("table"=>'tchat_sessions', "select"=>"count(*)", 'where'=>array("user_session"=>$newhash)));
			$hashExists = is_array($req) && count($req) != 0 && isset($req[0]['count(*)']) ? $req[0]['count(*)'] != 0 : False;
		} while ($hashExists);
		$sql->insert(array("table"=>'tchat_sessions', 'data'=>array("id_user"=>$id,"user_session"=>$newhash)));
		$req = $sql->select(array("table"=>'tchat_users', "select"=>"*", 'where'=>array("id_user"=>$id)));
		$req[0]['user_session'] = $newhash;
		return array($req[0], $newhash);
	}
	public static function update_user($user, $params){
		global $sql;
		$sql->update(array("table"=>'tchat_users', 'data'=>array(
			"user_name"=>$params['name'],
			"user_pass"=>$params['pass'],
			"user_email"=>$params['email']
		), 'where'=>array("id_user"=>$user['id_user'])));
		Request::setTimestamp('tchat_users', $user['id_user']);
	}
	public static function send_data($params, $timestamp, $user){
		
		if(isset($user['user_pass'])) unset($user['user_pass']);
		if(isset($user['user_enabled'])) unset($user['user_enabled']);
		$data = array();
		$data['timestamp'] = $timestamp;
		$data['user'] = $user;
		$data['users_connected'] = Chat::getUserConnected();
		echo json_encode($data);
	}
	

	public static function onUserDisconnected(){
		foreach(Chat::getUserConnected() as $user){
			Request::setTimestamp('tchat_connected', $user['id_user']);
		}
	}
	
	public static function onUserConnected($id_user){
		foreach(Chat::getUserConnected() as $user){
			Request::setTimestamp('tchat_connected', $user['id_user']);
		}		
	}
	
	public static function triggerUserDisconnected(){
		global $sql;
		$timeout = time() - CHAT_TIMEOUT + 2;
		$query = "SELECT * FROM `tchat_connected` WHERE `last_query` <= $timeout";
		$req = mysql::query($query);
		if(count($req) == 0) return False;
		foreach($req as $u){
			$sql->delete(array("table"=>'tchat_connected', "where"=>array("id_user"=>$u['id_user'])));
		}
		Chat::onUserDisconnected();
		return True;
	}
	
	public static function getUserConnected(){
		Chat::triggerUserDisconnected();
		$timeout = time() - CHAT_TIMEOUT;
		$query = "SELECT DISTINCT B.id_user, B.user_name FROM `tchat_connected` A ".
				 "INNER JOIN `tchat_users` B on B.id_user=A.id_user AND `last_query` > $timeout";
		$req = mysql::query($query);
		return $req;
	}
	
	public static function setUserConnected($id_user){
		global $sql;
		$req = $sql->select(array(
			"table"=>'tchat_connected', 
			"select"=>"*", 
			"where" => array("id_user"=>$id_user)));

		if(count($req) == 0){
			Chat::onUserConnected($id_user);
			$res = $sql->insert(array("table"=>'tchat_connected', 'data'=>array(
				"id_user"=>$id_user, 
				"last_query"=>time()
			)));
		}else{
			$res = $sql->update(array("table"=>'tchat_connected', 'data'=>array(
				"last_query"=>time()
			), 'where'=>array("id_user"=>$id_user)));
			
			$timeout = time() - CHAT_TIMEOUT;
			if($req[0]['last_query'] < $timeout){
				Chat::onUserConnected($id_user);
			}
		}

	}
	
}

