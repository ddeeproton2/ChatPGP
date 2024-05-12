<?php
header('Content-Type: text/plain');

$config = array(
	'mysql_address' => '127.0.0.1:53306',
	'mysql_username' => 'root',
	'mysql_password' => 'root',
	'mysql_database' => 'tchat1'
);

require_once('modules/mysql/mysqli.php');
require_once('modules/mysql/buildsql.php');
require_once('modules/files/files.php');

define("SERVER_TIMEOUT", 30); // 30 seconds
define("CHAT_TIMEOUT", 50); // 40 seconds


/*
$config = array(
	'mysql_address' => '192.168.138.1',
	'mysql_username' => 'dd',
	'mysql_password' => '1636',
	'mysql_database' => 'tchat1'
);
 */

/*

CREATE USER 'dd'@'192.168.138.2' IDENTIFIED BY '1636';

GRANT ALL PRIVILEGES ON *.* TO 'dd'@'192.168.138.2' WITH GRANT OPTION;

SHOW GRANTS FOR 'dd'@'192.168.138.2';

*/

$_DEBUG = new Debug("log.txt", false);

Request::allowGetMethod();

Request::controler();



// ================ CLASS ================
class Action {

	public static function get($params = array()){
		$login = Chat::login($params);
		//var_dump($login);
		//exit;
		list($user, $usession) = $login;
		
		//$timestamp = Request::waitForChanges($user);
		$timestamp = Request::waitForChangesFilesSystem($user);
		
		list($u, $usession) = Chat::currentUser(array('usersession'=>$usession));
		if($u != False) $user = $u;
		Chat::send_userdata($params, $timestamp, $user);
	}
	
	public static function uploadAudio($params = array()){
            
            list($user, $usession) = Chat::currentUser(array("usersession"=>$_POST['usersession']));
            //var_dump($user);
            if(!isset($user['id_user'])){
                echo 'id_user nos set';
                return;
            }
            if (!isset($_POST['audio-filename']) && !isset($_POST['video-filename'])) {
                echo 'PermissionDeniedError';
                return;
            }

            $fileName = '';
            $tempName = '';

            if (isset($_POST['audio-filename'])) {
                $fileName = $_POST['audio-filename'];
                $tempName = $_FILES['audio-blob']['tmp_name'];
            } else {
                $fileName = $_POST['video-filename'];
                $tempName = $_FILES['video-blob']['tmp_name'];
            }

            if (empty($fileName) || empty($tempName)) {
                echo 'PermissionDeniedError';
                return;
            }
            if(!is_dir('uploads')){ files::createDir('uploads');}
            $filePath = 'uploads/' . $user['id_user']."_".$fileName;

            // make sure that one can upload only allowed audio/video files
            $allowed = array(
                'webm',
                'wav',
                'mp4',
                'mp3',
                'ogg'
            );
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            if (!$extension || empty($extension) || !in_array($extension, $allowed)) {
                echo 'PermissionDeniedError';
                return;
            }

            if (!move_uploaded_file($tempName, $filePath)) {
                echo ('Problem saving file.');
                return;
            }

            echo ($filePath);
	}
	public static function setProfile($params = array()){
		list($user, $usession) = Chat::login($params);
		Chat::update_user($user, $params);
		list($user, $usession) = Chat::currentUser($params);
		Chat::send_userdata($params, '', $user);
	}

	public static function sendMessage($params = array()){
		global $_DEBUG; 
		$_DEBUG->add("Action::sendMessage(".var_export($params, true).")");
		/*
		Action::sendMessage(array (
			'usersession' => 'ac9a10ef3c249bbdebf09878caedf2f1',
			'messages' => 
			array (
			0 => 
			array (
				'message' => 'wUwDwY4l5IJa2MABAf99CZfN40D25NqjYEyw0CaLlA/GOkeePXQcxm5yE5GR
		ek48IEl9MfpoWmkIbVVeZMbYtHN94oHbJ585ySW9rz810j4BXYNhh7IaFAyr
		DndyNg0Fo4urqHIcz02nTwsk5fPAg0JMJxTeO+Poyh/4wiLEmP/W3HD8Hpw4
		WKk2SajghQ==
		=s6bx
		',
				'id_publickey' => '2147483647',
				'user_name' => 'guest_1',
				'id_user' => '1',
			),
			),
			'id_room' => '0',
		))
		*/
		
                
		list($user, $usession) = Chat::login($params);
		$_DEBUG->add("list(".var_export($user, true).",". $usession.") = Chat::login(".var_export($params, true).")");
		/*
			list(
			*      $user = array (
					'id_user' => '1',
					'user_privilege' => '0',
					'user_enabled' => '1',
					'user_name' => 'guest_1',
					'user_pass' => '',
					'user_email' => '',
					'user_description' => '',
					'user_allow_newsession' => '1',
					'user_notify_typing' => '1',
					'user_avatar' => '',
					'id_publickey' => '2147483647',
					'publickey' => '-----BEGIN PGP PUBLIC KEY BLOCK-----
									Version: OpenPGP.js v.1.20130420
									Comment: http://openpgpjs.org

									xk0EYmK5PAECAJg5ZpK3hHJkgCKy/7e5wFHVSKORdHcugUe9E1n+3Bb4AXsG
									GYRe9NJvXtCD2ngcTPfAJoavZWEiI/ah6V7tWaMAEQEAAc0jNWl3T1lSSXVP
									UHFONXVMbmRlZUhkVVYwNlpKVmU0UjkzYTbCXAQQAQgAEAUCYmK5PAkQwY4l
									5IJa2MAAAOwUAf9hzWCmZVxuf9tMq2fKZca/IBB+hRTdjuqcpmOz490dlOqh
									dI8Aphh9OgLFl3Rh8sKC+Cw7SgVlMQcDpfW8Jlcz
									=UGBO
									-----END PGP PUBLIC KEY BLOCK-----

					',
					'user_session' => 'ac9a10ef3c249bbdebf09878caedf2f1',
				),
				$usession = ac9a10ef3c249bbdebf09878caedf2f1
		) 
		= Chat::login(
			$params = array(
				'usersession' => 'ac9a10ef3c249bbdebf09878caedf2f1',
				'messages' => array (
					0 => array (
						'message' => 'wUwDwY4l5IJa2MABAf99CZfN40D25NqjYEyw0CaLlA/GOkeePXQcxm5yE5GR
										ek48IEl9MfpoWmkIbVVeZMbYtHN94oHbJ585ySW9rz810j4BXYNhh7IaFAyr
										DndyNg0Fo4urqHIcz02nTwsk5fPAg0JMJxTeO+Poyh/4wiLEmP/W3HD8Hpw4
										WKk2SajghQ==
										=s6bx
						',
						'id_publickey' => '2147483647',
						'user_name' => 'guest_1',
						'id_user' => '1',
					),
				),
				'id_room' => '0',
			)
		)
		*/
		
		
		//Chat::update_user($user, $params);
		Chat::setUserMessage($user, $params);
		list($user, $usession) = Chat::currentUser($params);
		//$timestamp = Request::waitTimestampLoogPool($user['id_user'], "");
		$timestamp = time();
		//Chat::send_userdata($params, $timestamp, $user);
		echo json_encode(array());

		$_DEBUG->add("Chat::send_data(".var_export($params, true).", '', ".var_export($user, true).")");
		/*
		Chat::send_data(array (
			'usersession' => 'ac9a10ef3c249bbdebf09878caedf2f1',
			'messages' => 
			array (
			0 => 
			array (
				'message' => 'wUwDwY4l5IJa2MABAf99CZfN40D25NqjYEyw0CaLlA/GOkeePXQcxm5yE5GR
				ek48IEl9MfpoWmkIbVVeZMbYtHN94oHbJ585ySW9rz810j4BXYNhh7IaFAyr
				DndyNg0Fo4urqHIcz02nTwsk5fPAg0JMJxTeO+Poyh/4wiLEmP/W3HD8Hpw4
				WKk2SajghQ==
				=s6bx
				',
				'id_publickey' => '2147483647',
				'user_name' => 'guest_1',
				'id_user' => '1',
			),
			),
			'id_room' => '0',
		), '', array (
			'id_user' => '1',
			'user_privilege' => '0',
			'user_enabled' => '1',
			'user_name' => 'guest_1',
			'user_pass' => '',
			'user_email' => '',
			'user_description' => '',
			'user_allow_newsession' => '1',
			'user_notify_typing' => '1',
			'user_avatar' => '',
			'id_publickey' => '2147483647',
			'publickey' => '-----BEGIN PGP PUBLIC KEY BLOCK-----
		Version: OpenPGP.js v.1.20130420
		Comment: http://openpgpjs.org

		xk0EYmK5PAECAJg5ZpK3hHJkgCKy/7e5wFHVSKORdHcugUe9E1n+3Bb4AXsG
		GYRe9NJvXtCD2ngcTPfAJoavZWEiI/ah6V7tWaMAEQEAAc0jNWl3T1lSSXVP
		UHFONXVMbmRlZUhkVVYwNlpKVmU0UjkzYTbCXAQQAQgAEAUCYmK5PAkQwY4l
		5IJa2MAAAOwUAf9hzWCmZVxuf9tMq2fKZca/IBB+hRTdjuqcpmOz490dlOqh
		dI8Aphh9OgLFl3Rh8sKC+Cw7SgVlMQcDpfW8Jlcz
		=UGBO
		-----END PGP PUBLIC KEY BLOCK-----

		',
			'user_session' => 'ac9a10ef3c249bbdebf09878caedf2f1',
		))

		*/
	}
	
	public static function openRoom($params = array()){
		list($user, $usession) = Chat::login($params);
		Chat::userRoomOpen($user, $params);
		
	}
	
	public static function disconnect($params = array()){
		list($user, $usession) = Chat::login($params);
		Chat::disconnect($user);
	}
	
	public static function resetdb($params = array()){
		//exit;
		$query = "DROP TABLE `tchat_bannedroomsuser`, `tchat_connected`, `tchat_messages`, `tchat_rooms`, `tchat_roomsusers`, `tchat_sessions`, `tchat_timestamp`, `tchat_users`;";
		$req = mysql::query($query);
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
			switch($_POST['action']){ 
				case "get":
					Action::get($_POST['params']);
					break;
				case "sendMessage":
					Action::sendMessage($_POST['params']);
					break;
				case "uploadAudio":
					Action::uploadAudio($_POST['params']);
					break;
				case "setProfile":
					Action::setProfile($_POST['params']);
					break;
				case "openRoom":
					Action::openRoom($_POST['params']);
					break;
				case "disconnect":
					Action::disconnect($_POST['params']);
					break;
				case "resetdb":
					Action::resetdb($_POST['params']);
					break;												
				default:
					die("Error: Action unkown (".$_POST['action'].")");
					//Action::$_POST['action']($_POST['params']);		
			}
			//Action::$_POST['action']($_POST['params']);
		}else{
            echo "Empty";
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
			if($noChange) sleep(1);
		}
		return $timestamp;
	}
	
	public static function waitForChangesFilesSystem($user){
		if(!isset($user['id_user'])) return "";
		Chat::setUserConnected($user['id_user']);
		set_time_limit(0);
		$now =  strtotime(date('Y-m-d H:i:s'));
		$user_timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : '';
        $timestamp = Request::waitTimestampLoogPool($user['id_user'], $user_timestamp);
        
		/*
		$noChange = true;
		while($noChange) {
			if(Request::isRequestTimeout($now)) break;
			$timestamp = Request::getTimestamp($user['id_user']);
			$noChange = $timestamp == $user_timestamp;
			//if($noChange) sleep(2);
		}
        */

		return $timestamp;
	}
	
	
	public static function getTimestampFilesSystem($id_user){
		$dir_timestamp = 'timestamp';
		if(!is_dir($dir_timestamp)) { files::createDir($dir_timestamp); }
		$dir = $dir_timestamp.'/'.$id_user;
		if(!is_dir($dir)) { return ""; }
		$file = $dir."/timestamp.txt"; 
		if(!is_file($file)) { return ""; }
		clearstatcache();
		return filemtime($file);
	}
	
	public static function setTimestampFilesSystem($table, $id_user){
		$dir_timestamp = 'timestamp';
		if(!is_dir($dir_timestamp)) { files::createDir($dir_timestamp); }
		$dir = $dir_timestamp.'/'.$id_user;
		if(!is_dir($dir)) { files::createDir($dir); }
		if(!is_dir($dir)) { return ""; }
		$file = $dir."/timestamp.txt"; 
		files::write($file, time());
	}
        
	public static function isRequestTimeout($now){
		return strtotime(date('Y-m-d H:i:s')) - $now > SERVER_TIMEOUT;
	}
	
	public static function setTimestampLoogPool($table, $id_user){
	    @file_get_contents("http://127.0.0.1:8088/add?id=".$id_user."&time=".time());
        }
        
        
	public static function waitTimestampLoogPool($id_user, $time){
            $result = @file_get_contents("http://127.0.0.1:8088/wait?id=".$id_user."&time=".$time);
            if($result) return $result;            
            return "";
        }
        
        
	public static function setTimestamp($table, $id_user){
		global $sql;
		$req = $sql->select(array("table"=>'tchat_timestamp', "select"=>"*", 'where'=>array("name"=>$table,"id_user"=>$id_user)));
		$newhash = time();
		if(count($req)!=0) {
			$sql->update(array("table"=>'tchat_timestamp', 'data'=>array("hash"=>$newhash), 'where'=>array("name"=>$table,"id_user"=>$id_user)));
		}else{
			$sql->insert(array("table"=>'tchat_timestamp', 'data'=>array("id_user"=>$id_user, "name"=>$table,"hash"=>$newhash)));
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
			if(isset($r['name'])){
				$hash[$r['name']] = $r['hash'];
                        }
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

		mysql::connect($config['mysql_address'], $config['mysql_username'], $config['mysql_password'], $config['mysql_database']);
		
		$structure = buildsql::get_structure();
		//$structure["users"]["pass"] = "pass";
		$sql = new buildsql($structure);
		//$form = new buildform($structure);
		Request::SQLCreateTables();
	}
	
	public static function SQLCreateTables(){
		global $sql;
		$sql->create_table('tchat_users', 'id_user_sql', array(
				"id_user" => "varchar(255)",
				"user_privilege" => "int(11)",
				"user_enabled" => "int(11)",
				"user_name" => "varchar(255)",
				"user_pass" => "varchar(255)",
				"user_email" => "varchar(255)",
				"user_description" => "varchar(255)",
				"user_allow_newsession" => "int(11)",
				"user_notify_typing" => "int(11)",
				//"user_colors" => "text",
				"user_avatar" => "text",
				"id_publickey" => "bigint(20)",
				"publickey" => "text"
			)
		);

		$sql->create_table('tchat_sessions', 'id_session', array(
				"id_user" => "varchar(255)",
				"user_session" => "varchar(255)"
			)
		);
		$sql->create_table('tchat_rooms', 'id_room', array(
				"id_creator" => "bigint(20)",
				"enabled" => "int(11)",
				"name" => "varchar(255)",
				"crypted" => "int(11)",
				"public" => "int(11)"
			)
		);
		$sql->create_table('tchat_roomsusers', 'id_roomsuser', array(
				"id_room" => "int(11)",
				"id_user" => "varchar(255)",
				"id_creator" => "bigint(20)"
			)
		);
		$sql->create_table('tchat_bannedroomsuser', 'id_bannedroomsuser', array(
				"id_room" => "int(11)",
				"id_user" => "varchar(255)"
			)
		);
		$sql->create_table('tchat_connected', 'id_connected', array(
				"id_user" => "varchar(255)",
				"last_query" => "int(11)"
			)
		);
		$sql->create_table('tchat_messages', 'id_message', array(
				"id_user_from" => "varchar(255)",
				"id_user_to" => "varchar(255)",
				"id_room" => "int(11)",
				"date_send" => "bigint(20)",
				"date_expire" => "bigint(20)",
				"date_read" => "bigint(20)",
				"id_publickey" => "bigint(20)",
				"message" => "text"
			)
		);
		$sql->create_table('tchat_timestamp', 'id_timestamp', array(
				"id_user" => "varchar(255)",
				"hash" => "varchar(255)",
				"name" => "varchar(255)"
			)
		);
	}
}

class Chat {
	public static function login($params = array(), $create_user = true){
			
            $curUser = Chat::currentUser($params);
			if($curUser == false){
				//die("Error, Chat::currentUser() is False");
				return Chat::create_newuser($params);
				die(json_encode(array("Error"=>"login fail")));
			}
			if(is_array($curUser) && count($curUser) > 1){
				list($user, $usession) = $curUser;
				if($user != False) return $curUser;
			}

            return False;
	}
	
	public static function disconnect($user){
            if(!isset($user['id_user'])){
                    die(json_encode(array("Error"=>"id_user not set")));
            }
            global $sql;
            $sql->delete(array("table"=>'tchat_connected', "where"=>array("id_user"=>$user['id_user'])));
            Chat::onUserDisconnected();
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
            //if($params['usersession'] == "null") return False;
			
            $req = $sql->select(array("table"=>'tchat_sessions', "select"=>"*", 'where'=>array("user_session"=>$params['usersession'])));
			
            if(!isset($req[0]) || !isset($req[0]['id_user'])) return False;
            $req = $sql->select(array("table"=>'tchat_users', "select"=>"*", 'where'=>array("id_user"=>$req[0]['id_user'])));
            if(is_array($req) && count($req) != 0 && isset($req[0]['id_user'])){
                    //Request::setTimestampLoogPool("", $req[0]['id_user']);
                    $req[0]['user_session'] = $params['usersession'];
                    return array($req[0], $params['usersession']);
            }
            return False;
	}
	
	public static function create_newuser($params = array()){
            global $sql;
            $nextid = Request::nextIDFromTable('tchat_users');
            if(!is_numeric($nextid)) die('Error: create_newuser() ($nextid is not numeric');
            //if(!isset($params['id_publickey'])) $params['id_publickey'] = 0;
            //if(!isset($params['publickey'])) $params['publickey'] = "";
            if(!isset($params['id_publickey']) || !isset($params['publickey'])) {
                die(json_encode(array("Error"=>"create user fail (public key is not set)")));
            }
            
            
            do {
                $newhash_userid = md5(time().rand(0,1000));
                $req = $sql->select(array("table"=>'tchat_users', "select"=>"count(*)", 'where'=>array("id_user"=>$newhash_userid)));
                $hashExists = is_array($req) && count($req) != 0 && isset($req[0]['count(*)']) ? $req[0]['count(*)'] != 0 : False;
            } while ($hashExists);
            
			//die($params['id_publickey']);
            $sql->insert(array("table"=>'tchat_users', 'data'=>array(
                    "id_user" => $newhash_userid,
                    "user_privilege"=>0,
                    "user_enabled"=>1,
                    "user_name"=>"guest_".$nextid,
                    "user_pass"=>"",
                    "user_email"=>"",
                    "user_description"=>"",
                    "user_allow_newsession"=>1,
                    "user_notify_typing"=>1,
					"user_avatar"=> "",
                    "id_publickey"=> $params['id_publickey'],
                    "publickey"=> $params['publickey']			
            )));
            /*
            $id = mysqli_insert_id(mysql::$bdd);
            if($id == false){
                die(json_encode(array("Error"=>"create user fail (table tchat_users)")));
            }
             * 
             */
            do {
                    $newhash = md5(time().rand(0,1000));
                    $req = $sql->select(array("table"=>'tchat_sessions', "select"=>"count(*)", 'where'=>array("user_session"=>$newhash)));
                    $hashExists = is_array($req) && count($req) != 0 && isset($req[0]['count(*)']) ? $req[0]['count(*)'] != 0 : False;
            } while ($hashExists);
            $sql->insert(array("table"=>'tchat_sessions', 'data'=>array("id_user"=>$newhash_userid,"user_session"=>$newhash)));
            /*
            $req = $sql->select(array("table"=>'tchat_users', "select"=>"*", 'where'=>array("id_user"=>$newhash_userid)));
            if(!isset($req[0]['user_name'])){
                    die(json_encode(array("Error"=>"create user fail")));
            }
             */
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
	
	public static function send_userdata($params, $timestamp, $user){
		
            if(isset($user['user_pass'])) unset($user['user_pass']);
            if(isset($user['user_enabled'])) unset($user['user_enabled']);
            $data = array();
            $data['timestamp'] = $timestamp;
            $data['user'] = $user;
            if(!isset($user['id_user'])) {
                //$data['error'] = "id_user is not set into send_userdata()";
                list($data, $user['id_user']) = Chat::create_newuser();
            }
            
            //else{
                $data['rooms'] = Chat::getUserRooms($user['id_user']);
                $data['users_connected'] = Chat::getUserConnected();
                if(!isset($params['lastIdMessage'])){ $params['lastIdMessage'] = -1; }
                $data['user_messages'] = Chat::getUserMessages($user['id_user'], $params['lastIdMessage']);
            //}
            echo json_encode($data);
	}

	public static function getUserMessages($id_user, $lastIdMessage){
            /*
            global $sql;
            $req = $sql->select(array(
                    "table"=>'tchat_messages', 
                    "select"=>"*", 
                    "where" => array("id_user_to"=>$id_user)));
             */
            /*
            $query = "SELECT * FROM `tchat_messages` WHERE `id_user_to` = $id_user AND `id_message` = ".$lastIdMessage;
            
		$query = "SELECT DISTINCT B.id_user, B.user_name, B.id_publickey, B.publickey FROM `tchat_connected` A ".
				 "INNER JOIN `tchat_users` B on B.id_user=A.id_user AND `last_query` > $timeout";
            
             
             */
            $query = "SELECT DISTINCT * FROM `tchat_messages` A ".
                    "INNER JOIN `tchat_users` B on B.id_user=A.id_user_from AND A.id_user_to = '".$id_user."';";// AND A.id_message > ".$lastIdMessage;
                    
            $req = mysql::query($query);
            
            //file_put_contents($id_user.".txt", json_encode($req));
            /*
            $query = "DELETE FROM `tchat_messages` WHERE `tchat_messages`.`id_message` <= '".$lastIdMessage."';";// AND `tchat_messages`.`id_user_to` = ".$id_user;      
            mysql::query($query);
            */
            return $req;
	}
	
	public static function setUserMessage($user, $params){
		global $sql, $_DEBUG;
		
            /*          
            $user = array (
                'id_user' => '1',
                'user_privilege' => '0',
                'user_enabled' => '1',
                'user_name' => 'guest_1',
                'user_pass' => '',
                'user_email' => '',
                'user_description' => '',
                'user_allow_newsession' => '1',
                'user_notify_typing' => '1',
                'user_avatar' => '',
                'id_publickey' => '2147483647',
                'publickey' => '-----BEGIN PGP PUBLIC KEY BLOCK-----
                                Version: OpenPGP.js v.1.20130420
                                Comment: http://openpgpjs.org

                                xk0EYmK5PAECAJg5ZpK3hHJkgCKy/7e5wFHVSKORdHcugUe9E1n+3Bb4AXsG
                                GYRe9NJvXtCD2ngcTPfAJoavZWEiI/ah6V7tWaMAEQEAAc0jNWl3T1lSSXVP
                                UHFONXVMbmRlZUhkVVYwNlpKVmU0UjkzYTbCXAQQAQgAEAUCYmK5PAkQwY4l
                                5IJa2MAAAOwUAf9hzWCmZVxuf9tMq2fKZca/IBB+hRTdjuqcpmOz490dlOqh
                                dI8Aphh9OgLFl3Rh8sKC+Cw7SgVlMQcDpfW8Jlcz
                                =UGBO
                                -----END PGP PUBLIC KEY BLOCK-----

                ',
                'user_session' => 'ac9a10ef3c249bbdebf09878caedf2f1',
            )


            $params = array(
            'usersession' => 'ac9a10ef3c249bbdebf09878caedf2f1',
            'messages' => array (
                0 => array (
                    'message' => 'wUwDwY4l5IJa2MABAf99CZfN40D25NqjYEyw0CaLlA/GOkeePXQcxm5yE5GR
                                  ek48IEl9MfpoWmkIbVVeZMbYtHN94oHbJ585ySW9rz810j4BXYNhh7IaFAyr
                                  DndyNg0Fo4urqHIcz02nTwsk5fPAg0JMJxTeO+Poyh/4wiLEmP/W3HD8Hpw4
                                  WKk2SajghQ==
                                  =s6bx
                    ',
                    'id_publickey' => '2147483647',
                    'user_name' => 'guest_1',
                    'id_user' => '1',
                ),
            ),
            'id_room' => '0',
        )



		*//*============
		$sql->create_table('tchat_messages', 'id_message', array(
				"id_user_from" => "int(11)",
				"id_user_to" => "int(11)",
				"id_room" => "int(11)",
				"date_send" => "int(11)",
				"date_expire" => "int(11)",
				"date_read" => "int(11)",
				"id_publickey" => "int(11)",
				"message" => "text"
		)
		*/
		if(!isset($params['messages'])) {
			echo '$params = '; var_dump($params); die("Erreur messages n'est pas définit dans la variable params. Fichier ".__FILE__." Ligne: ".__LINE__);
		}
		
		foreach($params['messages'] as $m){
			$data = array("id_user_from"=>$user['id_user'],
				"id_user_to"=>$m['id_user'],
				"id_room"=>$params['id_room'],
				"date_send"=>strtotime(date("Y-m-d H:i:s")),
				"date_expire"=>strtotime(date("Y-m-d H:i:s"))+1650805710,
				"date_read"=>'0',
				"id_publickey"=>$m['id_publickey'],
				"message"=>$m['message']
			);
			$sql->insert(array("table"=>'tchat_messages', 'data'=> $data));
			Request::setTimestamp('tchat_connected', $m['id_user']);
			$_DEBUG->add("tchat_message data = ".var_export($data, true));
		}
		
		self::onUserSendMessage();
		
	}

	public static function onUserSendMessage(){

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

		return;

		$timeout = time() - CHAT_TIMEOUT + 2;
		$query = "SELECT * FROM `tchat_connected` WHERE `last_query` <= $timeout";
		//$query = "SELECT * FROM `tchat_connected`";
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
		$query = "SELECT DISTINCT B.id_user, B.user_name, B.id_publickey, B.publickey FROM `tchat_connected` A ".
				 "INNER JOIN `tchat_users` B on B.id_user=A.id_user";
		/*
		$query = "SELECT DISTINCT B.id_user, B.user_name, B.id_publickey, B.publickey FROM `tchat_connected` A ".
				 "INNER JOIN `tchat_users` B on B.id_user=A.id_user AND `last_query` > $timeout";
		*/
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
	
	public static function createRoom($id_user, $name, $crypted, $public, $enabled){
		global $sql;
		$res = $sql->insert(array("table"=>'tchat_rooms', 'data'=>array(
			"id_creator"=>$id_user,
			"name"=>$name,
			"crypted"=>$crypted,
			"public"=>$public,
			"enabled"=>$enabled
		)));
	}
	
	public static function updateRoom($id_room, $id_user, $name, $crypted, $public, $enabled){
		global $sql;
		$res = $sql->update(array("table"=>'tchat_rooms', 'data'=>array(
			"id_creator"=>$id_user,
			"name"=>$name,
			"crypted"=>$crypted,
			"public"=>$public,
			"enabled"=>$enabled
		), 'where'=>array("id_room"=>$id_room)));
	}
	
	public static function deleteRoom($id_room){
		global $sql;
		$res = $sql->update(array("table"=>'tchat_rooms', 'data'=>array(
			"enabled"=>0
		), 'where'=>array("id_room"=>$id_room)));
	}
	
	public static function getUserRooms($id_user){
		global $sql;
		// Get public rooms
		$publicRooms = Chat::publicRooms();
		// if no room, create the main room for everyone
		if(count($publicRooms) == 0) {
			$name = "Le Tchat";
			$crypted = 0;
			$public = 1;
			$enabled = 1;
			Chat::createRoom($id_user, $name, $crypted, $public, $enabled);
			$publicRooms = Chat::publicRooms();
			if(count($publicRooms) == 0) {
				die('Error: Cannot create room');
			}
		}
		// Get and Open public rooms for current user
		$req = $sql->select(array(
			"table"=>'tchat_roomsusers', 
			"select"=>"*", 
			"where" => array(
				"id_user"=>$id_user
			)
		));
		// For less SQL request, we create an array "$roomusers" (not required)
		$roomsusers = array();
		foreach($req as $r){
			$roomsusers[$r['id_room']] = true;
		}
		// Open public rooms for current user
		foreach($publicRooms as $r){
			if(!isset($r['id_room'])) continue;
			Chat::userRoomOpen($id_user,  $r['id_room'], $roomsusers);
			$roomsusers[$r['id_room']] = true;
		}
		// Return current user room
		return Chat::userRooms($id_user);
	}
	

	public static function publicRooms(){
		global $sql;
		$req = $sql->select(array(
			"table"=>'tchat_rooms', 
			"select"=>"*", 
			"where" => array(
				"public"=> 1,
				"enabled"=>1
			)));
		if(isset($req['error'])) {
			die($req['errorMessage']);
		}
		return $req;
	}
	

	public static function userRooms($id_user){
		/*
		global $sql;
		$req = $sql->select(array(
			"table"=>'tchat_roomsusers', 
			"select"=>"*", 
			"where" => array(
				"id_user"=>$id_user
			)));
		*/
		if(!is_numeric($id_user)) return array();
		$query = "SELECT DISTINCT * ".
				 "FROM `tchat_rooms` A ".
				 "INNER JOIN `tchat_roomsusers` B ".
				 "INNER JOIN `tchat_users` C ".
				 " ON B.id_room = A.id_room ".
				 " AND B.id_user = C.id_user ".
				 " AND C.user_enabled = 1 ".
				 " AND A.enabled = 1 ".
				 " AND B.id_user = ".$id_user;
		$req = mysql::query($query);
		return $req;
	}
	
	public static function userRoomOpen($id_user, $id_room, $roomsusers = null){
		global $sql;
		if($roomsusers == null){
			$roomsusers = $sql->select(array(
				"table"=>'tchat_roomsusers', 
				"select"=>"*", 
				"where" => array(
					"id_user"=>$id_user,
					"id_room"=>$id_room
				)
			));
			if(count($roomsusers) == 0){
				$res = $sql->insert(array("table"=>'tchat_roomsusers', 'data'=>array(
					"id_user"=>$id_user,
					"id_room"=>$id_room,
					"id_creator"=>$id_user
				)));
			}
		}else{
			if(!isset($roomsusers[$id_room])){
				$res = $sql->insert(array("table"=>'tchat_roomsusers', 'data'=>array(
					"id_user"=>$id_user,
					"id_room"=>$id_room,
					"id_creator"=>$id_user
				)));
			}
		}


	}
	 
	public static function userRoomClose($id_user, $id_room){
		global $sql;
		$req = $sql->delete(array(
			"table"=>'tchat_roomsusers', 
			"where" => array(
				"id_user"=>$id_user,
				"id_room"=>$id_room
			)));
	}
	
}

class Debug{
    public $fp;
    public $enabled;
    public function __construct($filename, $enabled = true){
        $this->enabled = $enabled;
        if($this->enabled == false) { return; } 
        $this->fp = fopen($filename,"a"); 
    }
    
    public function add($log){
        if($this->enabled == false) { return; } 
        fputs($this->fp, self::format($log));
    }
    
    public function __destruct() {
        if($this->enabled == false) { return; } 
        fclose($this->fp);
    }
    
    private static function format($log){
        return "[".date("d.m.Y H:i:s")."] ".$log."\n\n";
    }
    
    public static function log($log){
        files::writeAppend("log.txt", self::format($log));
    }

}