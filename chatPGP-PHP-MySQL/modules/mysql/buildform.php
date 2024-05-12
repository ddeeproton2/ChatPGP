<?php

class buildform{

	public static $structure;
	public static $error;
	public static $colors;
	public static $status_message;
	public static $status_color;

	function __construct($structure){
		self::$structure = $structure;
		self::$error = false;
		self::$colors = array(
			'white' => 'default',
			'blue' => 'primary',
			'green' => 'success',
			'turquoise' => 'info',
			'orange' => 'warning',
			'red' => 'danger',
			'transparent' => 'link',
		);
	}

	
	public static function build($data, $params = array()) {
		if(!is_array($params)) $params = array();
		if(!isset($params['type'])) $params['type'] = "";
		switch($params['type']) {
			case "table":
				return self::table($data, $params);
				break;
			case "input":
				return self::input($data, $params);
				break;
			default:
				break;
		}
	}
	
	
	public static function build_html_button($name, $value , $type = 'submit', $color = 'white', $class = '', $title = '', $showTitle = true) {
		return '<div class="input-group">
					'.($showTitle?'<label>&nbsp;</label>':'').'
					<div class="clr"></div>
					<input type="'.$type.'" name="'.$name.'" class="'.$class.' btn btn-'.self::$colors[$color].'" value="'.$value.'" title="'.$title.'" />
				</div>';
	}
	public static function build_special_link($link, $data) {
		if(strpos($link, "{") === false) return $link;
		$tab = explode("{", $link);
		$return = $tab[0];
		unset($tab[0]);
		foreach($tab as $t) {
			$var = explode("}", $t);
			$varname = $var[0];
			if(isset($data[$varname])) {
				$return .= $data[$varname];
				if(isset($var[1])) $return .= $var[1];
			}else{
				$return .= "{".$t;
			}
		}
		return $return;
	}
	
	public static function build_html_button_dropdown($button, $data, $color = 'white', $class = '') {
		if(!isset($button['dropdown'])) return '$button[dropdown] n\'est pas définit.';
		if(!isset($button['text'])) return '$button[text] n\'est pas définit.';
		if(!isset($button['hint'])) $button['hint'] = '';
		if(!is_array($button['dropdown'])) return '$button[dropdown] dans build_html_button_dropdown()';
		if(count($button['dropdown'])==0) return '$button[dropdown] vide dans build_html_button_dropdown()';
		$return = '	<div class="btn-group">
						<button type="button" class="'.$class.' btn btn-'.self::$colors[$color].' dropdown-toggle"  title="'.$button['hint'].'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							'.$button['text'].' <span class="caret"></span>
						</button>
						<ul class="dropdown-menu">';
						foreach($button['dropdown'] as $text => $link) {
							$link = self::build_special_link($link, $data);
							if($link == "separator") {
								$return .= '<li role="separator" class="divider"></li>';
							}else{
								$return .= '<li><a href="'.$link.'">'.$text.'</a></li>';
							}
						}
		$return .= '	</ul>
					</div>';
		return $return;
	}
	public static function build_html_input($title, $name, $value , $type = 'text', $showTitle = true) {
		return ($type != "hidden" ? '
				<div class="input-group">
					'.($showTitle?'<label for="'.$name.'">'.$title.'</label>':'').'
					<div class="clr"></div>' : '').'
					<input type="'.$type.'" name="'.$name.'" id="'.$name.'" placeholder="'.$title.'" class="form-control" value="'.$value.'" />
				'.($type != "hidden" ? '</div>' : '');
	}
	private static function build_textarea($title, $name, $value) {
		return '<div class="input-group">
					<label for="'.$name.'">'.$title.'</label>
					<div class="clr"></div>
					<textarea name="'.$name.'" placeholder="'.$title.'" id="'.$name.'" class="form-control">'.$value.'</textarea>
					<div class="clr"></div>
				</div>';
	}
	public static function build_html_select($title, $name, $value, $select) {
		$return = '<div class="form-group">
					<label for="modify_target">'.$title.'</label>
					<div class="clr"></div>
					<select name="'.$name.'" id="'.$name.'" class="selectpicker">';
						foreach($select as $index => $val) { 
							$return .= '<option value="'.$index.'" '.($index == $value ? "selected":"").'>'.$val.'</option>';
							// DEBUG
							//$return .= '<option value="'.$index.'" '.($index == $value ? "selected":"").'>'.$val.'=('.$index.'=='.$value.')'.($index == $value ? "selected":"no").'</option>';
						}
		$return .= '</select>
				</div>';
		return $return;
	}
	public static function build_html_select_multiple($title, $name, $value, $select, $form_group_class = "form-group") {
		$return = '<div class="'.$form_group_class.'">
					<label for="modify_target">'.$title.'</label>
					<div class="clr"></div>
					<select name="'.$name.'[]" id="'.$name.'" class="selectpicker" multiple="multiple">';
						$i = 0;
						foreach($select as $index => $val) { 
							if(is_array($value)) {
								$selected = in_array($index,$value) ? "selected=true":"";
							}else{
								$selected = $index == $value ? "selected=true":"";
							}
							$return .= '<option value="'.$index.'" '.$selected.'>'.$val.'</option>';
							$i++;
						}
		$return .= '</select>
				</div>';
		return $return;
	}
	private static function build_datetime($title, $name, $value) {
		return '<div class="input-group">
					<label for="'.$name.'">'.$title.'</label>
					<div class="clr"></div>
					<div class="input-group date" id="datetimepicker_'.$name.'">
						<input type="text" name="'.$name.'" id="'.$name.'" class="form-control" value="'.$value.'" />
						<span class="input-group-addon">
							<span class="glyphicon glyphicon-calendar"></span>
						</span>
						<div class="clr"></div>
					</div>
				</div>
				<script type="text/javascript">
					$(function () {
						$(\'#datetimepicker_'.$name.'\').datetimepicker({
							language: "fr",
							todayBtn: true,
							autoclose: true,
							pickerPosition: "top-left",
							minuteStep: 5
						});
					});
				</script>';
	}
	
	private static function build_data($type, $name, $d, $params) {
		if(isset($params['select'][$name]) && is_array($params['select'][$name])) {
			$type = "select";
			$select = $params['select'][$name];
		}
		if(isset($params['titles'][$name])) {
			$title = $params['titles'][$name];
		}else{
			$title = $name;
		}
		$type = buildsql::structure_type($type);
		if($type == 'int' || $type == 'bigint') {
			return self::build_html_input($title, $name, $d, 'number');
		}elseif($type == 'varchar') {
			return self::build_html_input($title, $name, $d, 'text');
		}elseif($type == 'date' || $type == 'datetime') {
			return self::build_datetime($title, $name, $d);
		}elseif($type == 'text') {
			return self::build_textarea($title, $name, $d);
		}elseif($type == 'pass') {
			return self::build_html_input($title, $name, $d, 'pass');
		}elseif($type == 'hidden') {
			return self::build_html_input($title, $name, $d, 'hidden');
		}elseif($type == 'select') {
			return self::build_html_select($title, $name, $d, $select);
		}
	}
	
	public static function input($data, $params = array()) {
		if(count($data) == 0) {
			//return;
			return "vide";
		}
		if(!isset($params['table'])) 
			return array("error" => true,"errorMessage" => 'table n\'est pas définit dans $params.');
		
		if(!isset(self::$structure[$params['table']])) 
			return array("error" => true,"errorMessage" => 'table n\'existe pas dans $structure ['.$params['table'].'].');
		
		$hidden = isset($params['hidden']) && is_array($params['hidden']) ? $params['hidden'] : array();
		$structure = self::$structure[$params['table']];
		$result = '<div class="well">';
		if(isset($params['title']) && $params['title'] != '') { 
			$result .= '<h3>'.$params['title'].'</h3>';
		}
		$result .= '<form class="'.$params['table'].' navbar-form navbar-right" action="'.(isset($params['action']) ? $params['action'] : '').'" method="POST">';
		foreach($data as $title => $d) {
			if(!isset($structure[$title])) return array("error" => true,"errorMessage" => 'title n\'existe pas dans $structure ['.$title.'].');
			$type = $structure[$title];
			if(in_array($title, $hidden)) $type = "hidden";
			$result .= self::build_data($type, $title, $d, $params);
		}
		$result .= '<div class="clr"></div>';
		if(isset($params['buttons']) && is_array($params['buttons'])) {
			if(in_array('btnCancel', $params['buttons'])) $result .= self::build_html_button('btnCancel', 'Annuler', 'submit', 'white', 'class','Tout changement sera perdu');
			if(in_array('btnErase', $params['buttons'])) $result .= self::build_html_button('btnErase', 'Supprimer', 'submit', 'red', 'class','Effacer tout (ne sera plus disponible)');
			if(in_array('btnUpdate', $params['buttons'])) $result .= self::build_html_button('btnUpdate', 'Modifier', 'submit', 'orange', 'class','Enregistrer les modifications');
			if(in_array('btnAdd', $params['buttons'])) $result .= self::build_html_button('btnAdd', 'Ajouter', 'submit', 'green', 'class','Nouvelle donnée');
		}
		$result .= '</form><div class="clr"></div></div>';
		return $result;
	
	}
	
	public static function show_result_query($res, $successMessage) {
		if(isset($res['error'])) {
			self::$status_message .= $res['errorMessage']."<br>";
			self::$status_color = "red";
			//$debug .= "SQL\n".$res['errorMessage']."\n\n";
			return false;
		}else{
			if($successMessage != "") {
				self::$status_message .= $successMessage;
				self::$status_color = "green";
			}
			return true;					
		}
	}
		
	public static function showAlertMessage($message  = '', $color = 'red') {
		if($message != '') self::$status_message = $message;
		if($color != '') self::$status_color = $color;
		if(self::$status_message == '') return;
		$class = "";
		switch(self::$status_color){
			case "green":
				$class = 'alert-success';
				break;
			case "red":
				$class = 'alert-danger';
				break;
			case "blue":
				$class = 'alert-info';
				break;
			default:
				$class = 'alert-warning';
				break;
		}
		return '<div class="clr alert '.$class.'">'.self::$status_message.'</div>';
	}

		
	public static function pagination($url, $page, $start = 0) {
		$prev = $page - 1;
		$next = $page + 1;
		$return = '
			<nav>
			  <ul class="pager">
				'.($page>$start?'<li><a href="'.$url.'&page='.$prev.'">Précédant</a></li>':'').'
				<li><a href="'.$url.'&page='.$next.'">Suivant</a></li>
			  </ul>
			</nav>';
		return $return;
		
	}
	public static function table($data, $params = array()) {
		if(count($data) == 0) {
			return;
			//return "vide";
		}
		//$hidden = array('date_modification','date_created','iduser_creation','iduser_modification','erased');
		$hidden = array();
		if(isset($params['hidden']) && is_array($params['hidden'])) {
			$hidden = $params['hidden'];
		}
		$select = array();
		if(isset($params['select']) && is_array($params['select'])) {
			$select = $params['select'];
		}
		$titles = array();
		if(isset($params['titles']) && is_array($params['titles'])) {
			$titles = $params['titles'];
		}
		$result = '';
		if(isset($params["show_update_button"]) && $params["show_update_button"] == true) {
			$result .= '<form class="navbar-form navbar-right" action="" method="GET">';
			$result .= self::build_html_button('btnShowPanelAdding', 'Ajouter', 'submit', 'green', 'class', 'Ajouter un client');			
			if(isset($_GET['action_page']) && $_GET['action_page'] != '') {
				$result .= '<input type="hidden" name="action_page" value="'.$_GET['action_page'].'" />';
			}
			$result .= '</form>';
		}
		$result .= '<table class="tablesorter"><thead><tr>';
		if(isset($params["dropdown_buttons"]) && is_array($params["dropdown_buttons"])) {
			$result .= '<th data-sorter="false" class="sorter-false"></th>';
		}
		foreach($data as $d) {
			if(!is_array($d)) continue;
			foreach($d as $title => $d) {
				if(in_array($title, $hidden)) continue;
				if(isset($titles[$title])){
					$result .= '<th>'.$titles[$title].'</th>';
				}else{
					$result .= '<th>'.$title.'</th>';
				}
			}
			break;
		}

		if(isset($params["show_update_button"]) && $params["show_update_button"] == true) {
			$result .= '<th data-sorter="false">Bouttons</th>';
		}
		$rien = "";
		$result .= '</tr></thead><tbody>';
		foreach($data as $d) {
			if(!is_array($d)) continue;
			$result .= '<tr>';
			if(isset($params["dropdown_buttons"]) && is_array($params["dropdown_buttons"])) {
				$result .= '<td>';
				foreach($params["dropdown_buttons"] as $button) {
					$result .= self::build_html_button_dropdown($button, $d, 'white', 'class');
				}
				$result .= '</td>';
			}

			foreach($d as $title => $sqlvarname) {
				if(in_array($title, $hidden)) continue;
				if(isset($select[$title][$sqlvarname])) {
					$result .='<td>'.$select[$title][$sqlvarname].'</td>';
				}else{
					$result .='<td>'.$sqlvarname.'</td>';
				}
			}

			if(isset($params["show_update_button"]) && $params["show_update_button"] == true) {
				$result .='<td><form class="navbar-form navbar-right" action="" method="GET">';
				$result .= self::build_html_button('btnShowPanelModificator', 'Modifier', 'submit', 'orange', 'class', 'Modifier cette donnée');
				if(isset($d[$params['id_update_button']])) {
					$result .= '<input type="hidden" name="'.$params['id_update_button'].'" value="'.$d[$params['id_update_button']].'" />';
				}
				if(isset($_GET['action_page']) && $_GET['action_page'] != '') {
					$result .= '<input type="hidden" name="action_page" value="'.$_GET['action_page'].'" />';
				}
				$result .='</form></td>';
	
			/*
			$result .='<td>'
				.'	<form class="navbar-form navbar-right" action="" method="POST">'
				.'		<input type="submit" class="btn-warning btnAdminModifyd" name="btn_admin_d_modify" value="Modifier" />'
				.'		<input type="hidden" name="id_d" value="'.$rien.'" />'
				.'	</form>'
				.'	<form class="navbar-form navbar-right" action="" method="POST"  onsubmit="return confirm(\'Etes-vous sur de vouloir effacer cet utilisateur?\');">'
				.'		<input type="submit" class="btn-danger btnErase" name="btn_admin_d_erase" value="Effacer" />'
				.'		<input type="hidden" name="id_d" value="'.$rien.'" />'
				.'		<input type="hidden" name="action_form" value="admin_erase_d" />'
				.'	</form>'
				.'</td>'*/
			}
			$result .= '</tr>';
		}
		$result .= '</tbody></table>';
		return $result;
	
	}
	
	
	public static function getPostInputToArray($varnames = array(), $data = array(), $required = array()) {
		if(!is_array($varnames)) return array("error"=>true,"errorMessage"=>'$varnames n\'est pas un array dans getPostInputToArray($varnames, $data, $required)');
		if(!is_array($required)) return array("error"=>true,"errorMessage"=>'$required n\'est pas un array dans getPostInputToArray($varnames, $data, $required)');
		$errors = "";
		foreach($varnames as $var) {
			if(!isset($_POST[$var])) {
				$errors .= 'Champ manquant $_POST[\''.$var."']<br>";
				continue;
			}
			if(in_array($var, $required) && $_POST[$var] == '') return array("error"=>true,"errorMessage"=>'['.$var.'] ne doit pas être vide.');
			$data[$var] = $_POST[$var];
		}
		if($errors != "") {
			return array("error"=>true,"errorMessage"=>$errors);
		}else{
			return $data;
		}
	}
	
	/*
		<foreach:table>
			<data:varname>
		</foreach>
	*/
	

	
	public static function buildTemplate_foreach($template) {
		$result = "";
		if(strpos($template, "<foreach:") !== false) {
			$t = explode("<foreach:", $template);
			$result .= $t[0];
			unset($t[0]);
			foreach($t as $f) {
				//if($f[0] != ":") continue;
				if(strpos($f, "</foreach>") === false) return array("error"=>true,"errorMessage"=>'la balise [foreach] ne possède pas de fin </foreach>');
				$d = explode("</foreach>", $f);
				$foreach = $d[0];
				$afterForeach = $d[1];
				
				
				
				$d1 = explode(">", $f);
				$tableParams = $d1[0];
				
				$foreach = str_replace($tableParams.">", "", $foreach);
				
				$d2 = explode(":", $tableParams);
				$table = $d2[0];
				$where = array();
				if(strpos($table, "{") !== false && strpos($table, "}") !== false ) {
					list($table, $tparam) = explode("{", $table);
					$tparam = str_replace("}","",$tparam);
					
					foreach(explode(",", $tparam) as $tp) {
						if(strpos($tp, "=") === false) continue;
						list($i, $v) = explode("=", $tp);
						$where[$i] = $v;
					}
				}
				
				
				if(count($where)==0) { 
					$req = buildsql::select(array("table"=>$table));
					if(isset($req['error'])) return $req;
				}else{
					//echo '<textarea>';var_dump($where);echo '</textarea>';
					$req = buildsql::select(array("table"=>$table, "where"=>$where));
					if(isset($req['error'])) return $req;
				}
		
				if(count($req)!=0 
				&& strpos($foreach, "<foreach_before>") !== false
				&& strpos($foreach, "</foreach_before>") !== false) {
					$_t = explode("<foreach_before>", $foreach);
					$f1 = $_t[1];
					//$f1 = explode("<foreach_before>", $foreach)[1];
					//$before = explode("</foreach_before>", $f1)[0];
					$before = explode("</foreach_before>", $f1);
					$before = $before[0];
					$foreach = str_replace("<foreach_before>".$before."</foreach_before>", "", $foreach);
					$result .= $before;
				}
				$after = "";
				if(count($req)!=0 
				&& strpos($foreach, "<foreach_after>") !== false
				&& strpos($foreach, "</foreach_after>") !== false) {
					//$f1 = explode("<foreach_after>", $foreach)[1];
					$f1 = explode("<foreach_after>", $foreach);
					$f1 = $f1[1];
					//$after = explode("</foreach_after>", $f1)[0];
					$after = explode("</foreach_after>", $f1);
					$after = $after[0];
					$foreach = str_replace("<foreach_after>".$after."</foreach_after>", "", $foreach);
				}
				foreach($req as $dLine) {
					$res_line = $foreach;
					foreach($dLine as $title => $data) {
						if(strpos($res_line, "<data:".$title.">") !== false) {
							$res_line = str_replace("<data:".$title.">", $data, $res_line);
						}
						
						if(strpos($res_line, "<fordata:") !== false) {
							$fordata1 = explode("<fordata:", $res_line);
							unset($fordata1[0]);
							foreach($fordata1 as $fordata) {
								if(strpos($fordata, ":".$title."=") === false) continue;
								//
								//$fordata = explode("</fordata>", $fordata)[0];
								$fordata = explode("</fordata>", $fordata);
								$fordata = $fordata[0];
								//$fordataParams = explode(">", $fordata)[0];
								$fordataParams = explode(">", $fordata);
								$fordataParams = $fordataParam[0];
								
								$res_line = str_replace("<fordata:".$fordataParams.">", "", $res_line);
								$res_line = str_replace("</fordata>", "", $res_line);
								//echo '<textarea>';echo $fordata;echo '</textarea>';
								list($nameFordata, $linkId0, $table0) = explode(":", $fordataParams);
								
								$where0 = array();
								if(strpos($table0, "{") !== false && strpos($table0, "}") !== false ) {
									list($table0, $tparam0) = explode("{", $table0);
									$tparam0 = str_replace("}","",$tparam0);
									foreach(explode(",", $tparam0) as $tp) {
										if(strpos($tp, "=") === false) continue;
										list($i, $v) = explode("=", $tp);
										$where0[$i] = $v;
									}
								}
								if(strpos($linkId0, "=") !== false) {
									list($id, $id0) = explode("=", $linkId0);
									$where0[$id0] = $dLine[$id];
								}
								if(count($where0)==0) { 
									$req0 = buildsql::select(array("table"=>$table0));
									if(isset($req0['error'])) return $req0;
								}else{
									$req0 = buildsql::select(array("table"=>$table0, "where"=>$where0));
									if(isset($req0['error'])) return $req0;
								}
								
								//echo '<textarea>';echo $nameFordata;echo '</textarea>';
								if(strpos($res_line, "<fordata_before:".$nameFordata.">") !== false
								&& strpos($res_line, "</fordata_before:".$nameFordata.">") !== false) {
									//$f1 = explode("<fordata_before:".$nameFordata.">", $res_line)[1];
									$f1 = explode("<fordata_before:".$nameFordata.">", $res_line);
									$f1 = $f1[1];
									//$before = explode("</fordata_before:".$nameFordata.">", $f1)[0];
									$before = explode("</fordata_before:".$nameFordata.">", $f1);
									$before = $before[0];
									if(count($req0)==0) {
										$res_line = str_replace("<fordata_before:".$nameFordata.">".$before."</fordata_before:".$nameFordata.">", "", $res_line);
									}else{
										$res_line = str_replace("<fordata_before:".$nameFordata.">".$before."</fordata_before:".$nameFordata.">", $before, $res_line);
									}
									//$result .= $before;
								}
								//echo '<textarea>';echo $fordata;echo '</textarea>';
								if(strpos($res_line, "<fordata_after:".$nameFordata.">") !== false
								&& strpos($res_line, "</fordata_after:".$nameFordata.">") !== false) {
									//$f1 = explode("<fordata_after:".$nameFordata.">", $res_line)[1];
									$f1 = explode("<fordata_after:".$nameFordata.">", $res_line);
									$f1 = $f1[1];
									//$after0 = explode("</fordata_after:".$nameFordata.">", $f1)[0];
									$after0 = explode("</fordata_after:".$nameFordata.">", $f1);
									$after0 = $after0[0];
									if(count($req0)==0) {
										$res_line = str_replace("<fordata_after:".$nameFordata.">".$after0."</fordata_after:".$nameFordata.">", "", $res_line);
									}else{
										$res_line = str_replace("<fordata_after:".$nameFordata.">".$after0."</fordata_after:".$nameFordata.">", $after0, $res_line);
									}
								}
								//echo '<textarea>';echo $table0." ";var_dump($req0);echo '</textarea>';
								if(count($req0)==0) {
									if(strpos($res_line, "<fdata:".$table0.":") !== false) {
										$fd1 = str_replace($fordataParams.">", "", $fordata);
										$res_line = str_replace($fd1, "", $res_line);
										
									}
								}
								foreach($req0 as $dLine0) {
									//$res_line = $foreach;
									foreach($dLine0 as $title0 => $data0) {
										if(strpos($res_line, "<fdata:".$table0.":".$title0.">") !== false) {
											$res_line = str_replace("<fdata:".$table0.":".$title0.">", $data0, $res_line);
										}
									}
								}
		
								/*
								echo '<textarea>';
								echo $table0;
								echo '</textarea>';
								*/
							}
						}
					}
					$result .= $res_line;
				}
				
				if($after != '') $result .= $after;
				$result .= $afterForeach;
			}
		}

		return $result;
	}
	public static function buildTemplate($templateFile) {
		if(!isset($templateFile) || !is_file($templateFile)) return array("error"=>true,"errorMessage"=>'$params[templateFile] n\'est pas définit ou ne pointe pas sur un fichier');
		$template = files::read($templateFile);
		$result = self::buildTemplate_foreach($template);
		return $result;
	}
	
}

?>