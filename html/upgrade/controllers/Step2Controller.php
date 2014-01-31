<?php

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

require_once(dirname(__FILE__).'/StepController.php');

// docebo ce versions series 3.x.x ( 03xxx formely 3xxx )
// docebo ce versions series 4.x.x ( 04xxx formely 4xxx )
// forma     versions series 1.x   ( 1xxxx )

Class Step2Controller extends StepController {

	public $step = 2;

	public function validate() {
		$_SESSION['start_version'] = Get::req('start_version', DOTY_ALPHANUM, '3603');
		return true;
	}

	public function getNextStep($current_step) {
		$version = Get::req('start_version', DOTY_ALPHANUM, '3603');
		if ( version_compare($version, '3600','>=')  &&
		     version_compare($version, '4000','<' ) ) {
			//docebo ce v 3.x.x => go to step 3 (config upgrade )
			$next_step = $current_step + 1;
		}
		else if ( version_compare($version, '4000','>=') &&
		          version_compare($version, '5000','<' )) {
			//docebo ce v 4.x.x => go to step 3 (config upgrade )
			$next_step = $current_step + 1;
		}
		else {
			// forma v1.x => skip step 3 and 4 (config upgrade, db upgrade from 3 to 4)
			$next_step = $current_step + 3;  //
		}
		return ($next_step);
	}


	function connectToDb() {
		require_once(_base_.'/config.php');

		if (isset($GLOBALS['dbhost'])) {
			$_SESSION['db_info']['db_host'] = $GLOBALS['dbhost']; //$cfg['db_host'];
			$_SESSION['db_info']['db_user'] = $GLOBALS['dbuname']; //$cfg['db_user'];
			$_SESSION['db_info']['db_pass'] = $GLOBALS['dbpass']; //$cfg['db_pass'];
			$_SESSION['db_info']['db_name'] = $GLOBALS['dbname']; //$cfg['db_name'];
		}
		else if (isset($GLOBALS['cfg']['db_host'])) {
			$_SESSION['db_info']['db_host'] = $GLOBALS['cfg']['db_host'];
			$_SESSION['db_info']['db_user'] = $GLOBALS['cfg']['db_user'];
			$_SESSION['db_info']['db_pass'] = $GLOBALS['cfg']['db_pass'];
			$_SESSION['db_info']['db_name'] = $GLOBALS['cfg']['db_name'];
		}

		$db = mysql_connect($_SESSION['db_info']['db_host'], $_SESSION['db_info']['db_user'], $_SESSION['db_info']['db_pass']);
		mysql_select_db($_SESSION['db_info']['db_name']);

		return $db;
	}


	public function getCurrentVersion() {
		$db =$this->connectToDb();
		list($current_version) = mysql_fetch_row(mysql_query("SELECT param_value FROM core_setting WHERE param_name = 'core_version' "));
		mysql_close($db);

		$current_version =getVersionIntNumber($current_version);

		return $current_version;
	}


	function versionList() {

		$current_version =$this->getCurrentVersion();
		$end_version =getVersionIntNumber($GLOBALS['cfg']['endversion']);

		if ($current_version == $end_version) {
			$current_version =end(array_keys($GLOBALS['cfg']['versions']));
		}

		$txt = '<select id="start_version" name="start_version">';
		foreach($GLOBALS['cfg']['versions'] as $k => $v) {

			$txt .= '<option value="'.$k.'"'.($k == $current_version ? ' selected="selected"' : '' ).'>'.$v.'</option>';
		}
		$txt .= '</select>';
		return $txt;
	}

	function checkRequirements() {
		$res =array();

		// phpversion();
		$res['php']=((version_compare(PHP_VERSION, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.4.0', '<=')) ? 'ok' : 'err');
		// mysql client version, in php the version number is a string regcut it
		preg_match( '/([0-9]+\.[\.0-9]+)/', mysql_get_client_info(), $version );
		if(empty($version[1])) $res['mysql']='ok';
		else $res['mysql']=(version_compare($version[1], '5.0') >= 0 ? 'ok' : 'err');
		$res['xml']=(extension_loaded('domxml') ? 'ok' : 'err');
		$res['ldap']=(extension_loaded('ldap') ? 'ok' : 'err');
		$res['mbstring']=(extension_loaded('mbstring') ? 'ok' : 'err');
		$res['strict_mode']=($this->checkStrictMode() ? 'ok' : 'err');
		$res['mime_ct']=(function_exists('mime_content_type') || (class_exists('file') && method_exists('finfo', 'file')) ? 'ok' : 'err');

		$current_version =$this->getCurrentVersion();
		$end_version =getVersionIntNumber($GLOBALS['cfg']['endversion']);

		if ($current_version == $end_version) {
			$res['upg_not_needed']=true;
		}

		if ( version_compare($current_version, '3600','>=')  &&
		     version_compare($current_version, '4000','<') ) {
			// docebo ce versions series 3.x.x.x
			// Upgrader: we check if we are starting from a valid (old) config.php file:
			require_once(_base_.'/config.php');
			$res['config_v3']=(!empty($GLOBALS['dbhost']) ? 'ok' : 'err');
			$res['config_v4']='err';
			$res['config_v1']='err';
		}
		else {
			require_once(_base_.'/config.php');
			$res['config_v4']=(!empty($GLOBALS['cfg']['db_host']) ? 'ok' : 'err');
			$res['config_v3']='err';
			$res['config_v1']='ok';
		}

		return $res;
	}


	function checkStrictMode() {
		$db =$this->connectToDb();

		$qtxt ="SELECT @@GLOBAL.sql_mode AS res";
		$q =mysql_query($qtxt, $db);
		list($r1)=mysql_fetch_row($q);
		$qtxt ="SELECT @@SESSION.sql_mode AS res";
		$q =mysql_query($qtxt, $db);
		list($r2)=mysql_fetch_row($q);
		$res =((strpos($r1.$r2, 'STRICT_') === false) ? true : false);

		mysql_close($db);

		return $res;
	}


	function checkFolderPerm() {
		$res ='';

		$platform_folders=$_SESSION['platform_arr'];
		$file_to_check=array("config.php");
		$dir_to_check=array();
		$empty_dir_to_check=array();

		// common dir to check
		$dir_to_check = array(
			'files/tmp',
			'files/common/comment',
			'files/common/iofiles',
			'files/common/users'
			);

		$current_version =$this->getCurrentVersion();
		if ( version_compare($current_version, '3600','>=')  &&
		     version_compare($current_version, '5000','<') ) {
			$dirprefix = 'docebo';
		} else {
			$dirprefix = 'app';
		}

		foreach($platform_folders as $platform_code=>$dir_name) {

			$specific_file_to_check =array();
			$specific_dir_to_check =array();

			if(!is_dir(_base_.'/'.$dir_name.'/')) {
				$install[$platform_code]=FALSE;
			}
			else {
				$install[$platform_code] = TRUE;

				$empty_specific_dir_to_check = NULL;

				switch ($platform_code) {

					case "lms": {
						$specific_dir_to_check = array(
							'files/' . $dirprefix .'Lms/certificate',
							'files/' . $dirprefix .'Lms/chat',
							'files/' . $dirprefix .'Lms/forum',
							'files/' . $dirprefix .'Lms/item',
							'files/' . $dirprefix .'Lms/label',
							'files/' . $dirprefix .'Lms/message',
							'files/' . $dirprefix .'Lms/project',
							'files/' . $dirprefix .'Lms/repo_light',
							'files/' . $dirprefix .'Lms/sponsor',
							'files/' . $dirprefix .'Lms/test'
							 );

						if ( $dirprefix == 'app' ) {
							// new folders in formalms
							$specific_dir_to_check[] = 'files/' . $dirprefix .'Lms/htmlpages';
						}
						$empty_specific_dir_to_check = array(
							'files/' . $dirprefix .'/course',
							'files/' . $dirprefix .'/scorm'
						);
					} break;

					case "framework": {
						$specific_dir_to_check = array(
							'files/' . $dirprefix .'Core/field',
							'files/' . $dirprefix .'Core/photo',
							'files/' . $dirprefix .'Core/newsletter',
							'files/common/users'
						);

					} break;

				}

				$dir_to_check=array_merge($dir_to_check, $specific_dir_to_check);
				$file_to_check =array_merge($file_to_check , $specific_file_to_check);

				if ((is_array($specific_dir_to_check)) && (count($specific_dir_to_check) > 0) && (is_array($empty_specific_dir_to_check)))
					$empty_dir_to_check=array_merge($empty_dir_to_check, $empty_specific_dir_to_check);
			}
		}

		// Write permission
		$checked_dir 	= array();
		foreach($dir_to_check as $dir_name) {

			if(!is_dir(_base_.'/'.$dir_name.'/')) {
				$checked_dir[] = $dir_name;
			} elseif(!is_writable(_base_.'/'.$dir_name.'/')) {
				$checked_dir[] = $dir_name;
			}
		}
		if(!empty($checked_dir)) {

			$res.='<h3 class="err">'.Lang::t('_CHECKED_DIRECTORIES').'</h3>'
				.'<ul class="info"><li class="err"><span>'.implode('</span></li><li class="err"><span>',$checked_dir).'</span></li></ul>';
		}

		$checked_file 	= array();
		foreach($file_to_check as $file_name) {
			if(!is_writable(_base_.'/'.$file_name)) {
				$checked_file[] = $file_name;
			}
		}
		if(!empty($checked_file)) {

			$res.='<h3>'.Lang::t('_CHECKED_FILES').'</h3>'
				.'<ul class="info"><li class="err">'.implode('</li><li class="err">',$checked_file).'</li></ul>';
		}

		return $res;
	}

}

?>