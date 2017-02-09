    <?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

class CatalogLms extends Model
{
	var $edition_man;
	var $course_man;
	var $classroom_man;

	var $cstatus;
	var $acl_man;

    /* category handling */
    var $children;
    var $the_tree;
    var $tree_deep;

	public function  __construct()
	{
		require_once(_lms_.'/lib/lib.course.php');
		require_once(_lms_.'/lib/lib.edition.php');
		require_once(_lms_.'/lib/lib.date.php');

		$this->course_man = new Man_Course();
		$this->edition_man = new EditionManager();
		$this->classroom_man = new DateManager();

		$this->cstatus = array(	CST_PREPARATION => '_CST_PREPARATION',
								CST_AVAILABLE 	=> '_CST_AVAILABLE',
								CST_EFFECTIVE 	=> '_CST_CONFIRMED',
								CST_CONCLUDED 	=> '_CST_CONCLUDED',
								CST_CANCELLED 	=> '_CST_CANCELLED');

		$this->acl_man =& Docebo::user()->getAclManager();
	}

    
    
    public function enrolledStudent($idCourse){
            $query =    "SELECT COUNT(*)"
                ." FROM %lms_courseuser"
                ." WHERE idCourse = '".$idCourse."'";
        
        
        list($enrolled) = sql_fetch_row(sql_query($query));
        return $enrolled;
    }
    

    public function getInfoEnroll($idCourse,$idUser){
                $query =    "SELECT status, waiting, level"
                            ." FROM %lms_courseuser"
                            ." WHERE idCourse = ".$idCourse
                            ." AND idUser = ".$idUser;
                $result_control = sql_query($query);                
                return $result_control;
    }
    
    
    public function getInfoLO($idCourse){
        
          $query_lo =    "select org.idOrg, org.idCourse, org.objectType from (SELECT o.idOrg, o.idCourse, o.objectType 
              FROM %lms_organization AS o WHERE o.objectType != '' AND o.idCourse IN (".$row['idCourse'].") ORDER BY o.path) as org 
              GROUP BY org.idCourse";
              
              $result_lo = sql_query($query_lo);   
              return $result_lo;
    }
    
    
	public function getCourseList($type = '', $page = 1)
	{
        require_once(_lms_.'/lib/lib.catalogue.php');
		$cat_man = new Catalogue_Manager();

		$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());

		switch($type)
		{
			case 'elearning':
				$filter = " AND course_type = '".$type."'";
				$base_link = 'index.php?r=catalog/elearningCourse&amp;page='.$page;
				if(count($user_catalogue) > 0)
				{
					$courses = array();

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					//$filter .= " AND idCourse IN (".implode(',', $courses).")";
				}
			break;
			case 'classroom':
				$filter = " AND course_type = '".$type."'";
				$base_link = 'index.php?r=catalog/classroomCourse&amp;page='.$page;
				if(count($user_catalogue) > 0)
				{
					$courses = array();

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					//$filter .= " AND idCourse IN (".implode(',', $courses).")";
				}
			break;
			case 'new':
				$filter = " AND create_date >= '".date('Y-m-d', mktime(0, 0, 0, date('m'), ((int)date('d') - 7), date('Y')))."'";
				$base_link = 'index.php?r=catalog/newCourse&amp;page='.$page;
				if(count($user_catalogue) > 0)
				{
					$courses = array();

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

				//	$filter .= " AND idCourse IN (".implode(',', $courses).")";
				}
			break;
			case 'catalogue':
				$id_catalogue = Get::req('id_cata', DOTY_INT, '0');
				$base_link = 'index.php?r=catalog/catalogueCourse&amp;id_cat='.$id_catalogue.'&amp;page='.$page;

				$catalogue_course =& $cat_man->getCatalogueCourse($id_catalogue);
				$filter = " AND idCourse IN (".implode(',', $catalogue_course).")";
			break;
			default:
				$filter = '';
				$base_link = 'index.php?r=catalog/allCourse&amp;page='.$page;

               // var_dump($user_catalogue);
                
				if(count($user_catalogue) > 0)
				{
					$courses = array();

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

				//	$filter .= " AND idCourse IN (".implode(',', $courses).")";
				}
			break;
		}

		if(count($user_catalogue) == 0 && Get::sett('on_catalogue_empty', 'off') == 'off') {
			$filter = " AND 0 "; //query won't return any results with this setting
		}

		$limit = ($page - 1) * Get::sett('visuItem');
		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$query =	"SELECT *"
					." FROM %lms_course"
					." WHERE status NOT IN (".CST_PREPARATION.", ".CST_CONCLUDED.", ".CST_CANCELLED.")"
					." AND course_type <> 'assessment'"
					." AND (                       
						(can_subscribe=2 AND (sub_end_date = '0000-00-00' OR sub_end_date >= '".date('Y-m-d')."') AND (sub_start_date = '0000-00-00' OR '".date('Y-m-d')."' >= sub_start_date)) OR
                        (can_subscribe=1)
					) "
                    
					.$filter
					.($id_cat > 0 ? " AND idCategory = ".(int)$id_cat : '')
					." ORDER BY name";

		$result = sql_query($query);
        return $result; 
        
      
	}

	public function getTotalCourseNumber($type = '')
	{
		require_once(_lms_.'/lib/lib.catalogue.php');
		$cat_man = new Catalogue_Manager();

		$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());

		switch($type)
		{
			case 'elearning':
				$filter = " AND course_type = '".$type."'";
				if(count($user_catalogue) > 0)
				{
					$courses = array();

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (".implode(',', $courses).")";
				}
			break;
			case 'classroom':
				$filter = " AND course_type = '".$type."'";
				if(count($user_catalogue) > 0)
				{
					$courses = array();

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (".implode(',', $courses).")";
				}
			break;
			case 'edition':
				$filter = " AND course_edition = 1";
				if(count($user_catalogue) > 0)
				{
					$courses = array();

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (".implode(',', $courses).")";
				}
			break;
			case 'new':
				$filter = " AND create_date >= '".date('Y-m-d', mktime(0, 0, 0, date('m'), ((int)date('d') - 7), date('Y')))."'";
				if(count($user_catalogue) > 0)
				{
					$courses = array();

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (".implode(',', $courses).")";
				}
			break;
			case 'catalogue':
				$id_catalogue = Get::req('id_cata', DOTY_INT, '0');

				$catalogue_course =& $cat_man->getCatalogueCourse($id_catalogue);
				$filter = " AND idCourse IN (".implode(',', $catalogue_course).")";
			break;
			default:
				$filter = '';

				if(count($user_catalogue) > 0)
				{
					$courses = array();

					foreach($user_catalogue as $id_cat)
					{
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					$filter .= " AND idCourse IN (".implode(',', $courses).")";
				}
			break;
		}

		if(count($user_catalogue) == 0 && Get::sett('on_catalogue_empty', 'off') == 'off') {
			$filter = " AND 0 "; //query won't return any results with this setting
		}

		$id_cat = Get::req('id_cat', DOTY_INT, 0);

		$query =	"SELECT COUNT(*)"
					." FROM %lms_course"
					." WHERE status NOT IN (".CST_PREPARATION.", ".CST_CONCLUDED.", ".CST_CANCELLED.")"
					." AND course_type <> 'assessment'"
					." AND ("
					." date_begin = '0000-00-00'"
					." OR date_begin > '".date('Y-m-d')."'"
					." )"
					.$filter
					.($id_cat > 0 ? " AND idCategory = ".(int)$id_cat : '')
					." ORDER BY name";

		list($res) = sql_fetch_row(sql_query($query));

		return $res;
	}

	public function getUserCatalogue($id_user)
	{
		require_once(_lms_.'/lib/lib.catalogue.php');
		$cat_man = new Catalogue_Manager();

		$res =& $cat_man->getUserAllCatalogueInfo($id_user);

		return $res;
	}

	public function getUserCoursepath($id_user)
	{
		$user_catalogue = array_keys($this->getUserCatalogue($id_user));

		$query =	"SELECT idEntry"
					." FROM %lms_catalogue_entry"
					." WHERE idCatalogue IN (".implode(',', $user_catalogue).")"
					." AND type_of_entry = 'coursepath'";

		$result = sql_query($query);
		$res = array();

		while(list($id_path) = sql_fetch_row($result))
			$res[$id_path] = $id_path;

		return $res;
	}

	public function getUserCoursepathSubscription($id_user)
	{
		$query =	"SELECT id_path"
					." FROM %lms_coursepath_user"
					." WHERE idUser = '".$id_user."'";

		$result = sql_query($query);
		$res = array();

		while(list($id_path) = sql_fetch_row($result))
			$res[$id_path] = $id_path;

		return $res;
	}

	public function getCoursepathList($id_user, $page)
	{
		$html = '';
		$coursepath = $this->getUserCoursepath($id_user);
		$user_coursepath = $this->getUserCoursepathSubscription($id_user);
		$limit = ($page - 1) * Get::sett('visuItem');

		$query =	"SELECT id_path, path_name, path_code, path_descr, subscribe_method"
					." FROM %lms_coursepath"
					." WHERE id_path IN (".implode(',', $coursepath).")"
					." LIMIT ".$limit.", ".Get::sett('visuItem');

		$result = sql_query($query);

		while(list($id_path, $name, $code, $descr, $subscribe_method) = sql_fetch_row($result))
		{
			$action = '';
			if(isset($user_coursepath[$id_path]))
				$action = '<div class="catalog_action"><p class="subscribed">'.Lang::t('_USER_STATUS_SUBS', 'catalogue').'</p></div>';
			elseif ($subscribe_method != 0)
				$action = "<div class=\"catalog_action\" id=\"action_".$id_path."\"><a href=\"javascript:void(0);\" onclick=\"subscriptionCoursePathPopUp('".$id_path."')\" title=\"Subscribe\"><p class=\"can_subscribe\">".Lang::t('_SUBSCRIBE', 'catalogue')."</p></a></div>";
			elseif ($subscribe_method == 0)
				$action .= '<div class="catalog_action"><p class="cannot_subscribe">'.Lang::t('_COURSE_S_GODADMIN', 'catalogue').'</p></div>';

			$html .=	'<div style="position:relative;clear: none;margin: .4em 1em 1em;padding-bottom:1em;border-bottom:1px solid #BAC2CF;">'
						.'<h2>'
						.$name
						.'</h2>'
						.'<p class="course_support_info">'
						.$descr
						.'</p>'
						.'<p style="padding:.4em">'
						.($code ? '<i style="font-size:.88em">['.$code.']</i>' : '')
						.'</p>'
						.''//lista corsi
						.$action
						.'</div>';
		}

		return $html;
	}

	public function subscribeCoursePathInfo($id_path)
	{

		$res = array();

		$res['success'] = true;
		$res['title'] = Lang::t('_COURSEPATH_SUBSCRIBE_WIN_TIT', 'catalogue');
		$res['body'] = Lang::t('_COURSEPATH_SUBSCRIBE_WIN_TXT', 'catalogue');
		$res['footer'] = '<a href="javascript:void(0);" onclick="subscribeToCoursePath(\''.$id_path.'\');"><span class="close_dialog">'.Lang::t('_SUBSCRIBE', 'catalogue').'</span></a>'
							.'&nbsp;&nbsp;<a href="javascript:void(0);" onclick="hideDialog();"><span class="close_dialog">'.Lang::t('_UNDO', 'catalogue').'</span></a>';
		return $res;
	}


	public function subscribeInfo($id_course, $id_date, $id_edition, $selling)
	{
		$res = array();

		if($id_date != 0)
		{
			$classroom_info = $this->classroom_man->getDateInfo($id_date);

			$res['success'] = true;

			if($selling ==1)
				$res['title'] = Lang::t('_CONFIRM_ADD_TO_CART', 'catalogue');
			else
				$res['title'] = Lang::t('_CONFIRM_SUBSCRIPTION', 'catalogue');

			$res['body'] =	Lang::t('_NAME', 'catalogue').': '.$classroom_info['name'].'<br/>'
							.($classroom_info['code'] !== '' ? Lang::t('_CODE', 'catalogue').': '.$classroom_info['code'].'<br/>' : '')
							.($classroom_info['date_begin'] !== '0000-00-00' ? Lang::t('_DATE_BEGIN', 'course').': '.Format::date($classroom_info['date_begin'], 'date').'<br/>' : '')
							.($classroom_info['date_end'] !== '0000-00-00' ? Lang::t('_DATE_END', 'course').': '.Format::date($classroom_info['date_end'], 'date').'<br/>' : '');
							//.'<a href="javascript:void(0);" onclick="hideDialog();"><p class="close_dialog">'.Lang::t('_UNDO', 'catalogue').'</p></a>'

			$is_in_overbooking = $classroom_info['max_par'] <= $classroom_info['user_subscribed'] && $classroom_info['overbooking'] > 0;
			if ($is_in_overbooking) {
				$res['body'] .= '<br /><p class="red"><b>'.Lang::t('_OVERBOOKING_WARNING', 'catalogue').'</b></p><br />';
			}

			$res['footer'] = ($selling == 1 ? '<a href="javascript:void(0);" onclick="subscribeToCourse(\''.$id_course.'\', \''.$id_date.'\', \''.$id_edition.'\', \''.$selling.'\');"><span class="close_dialog">'.Lang::t('_CONFIRM', 'catalogue').' ('.$classroom_info['price'].' '.Get::sett('currency_symbol', '&euro;').')'.'</span></a>'
								: '<a href="javascript:void(0);" onclick="subscribeToCourse(\''.$id_course.'\', \''.$id_date.'\', \''.$id_edition.'\', \''.$selling.'\');"><span class="close_dialog">'.Lang::t('_SUBSCRIBE', 'catalogue').'</span></a>')
							.'&nbsp;&nbsp;<a href="javascript:void(0);" onclick="hideDialog();"><span class="close_dialog">'.Lang::t('_UNDO', 'catalogue').'</span></a>';
		}
		elseif($id_edition != 0)
		{
			$edition_info = $this->edition_man->getEditionInfo($id_edition);
			$res['success'] = true;

			if($selling ==1)
				$res['title'] = Lang::t('_CONFIRM_ADD_TO_CART', 'catalogue');
			else
				$res['title'] = Lang::t('_CONFIRM_SUBSCRIPTION', 'catalogue');

			$res['body'] =	Lang::t('_NAME', 'catalogue').': '.$edition_info['name'].'<br/>'
							.($edition_info['code'] !== '' ? Lang::t('_CODE', 'catalogue').': '.$edition_info['code'].'<br/>' : '')
							.($edition_info['date_begin'] !== '0000-00-00' ? Lang::t('_DATE_BEGIN', 'course').': '.Format::date($edition_info['date_begin'], 'date').'<br/>' : '')
							.($edition_info['date_end'] !== '0000-00-00' ? Lang::t('_DATE_END', 'course').': '.Format::date($edition_info['date_end'], 'date').'<br/>' : '');
							//.'<a href="javascript:void(0);" onclick="hideDialog();"><p class="close_dialog">'.Lang::t('_UNDO', 'catalogue').'</p></a>';
			$res['footer'] = ($selling == 1 ? '<a href="javascript:void(0);" onclick="subscribeToCourse(\''.$id_course.'\', \''.$id_date.'\', \''.$id_edition.'\', \''.$selling.'\');"><span class="close_dialog">'.Lang::t('_CONFIRM', 'catalogue').' ('.$edition_info['price'].' '.Get::sett('currency_symbol', '&euro;').')'.'</span></a>'
											: '<a href="javascript:void(0);" onclick="subscribeToCourse(\''.$id_course.'\', \''.$id_date.'\', \''.$id_edition.'\', \''.$selling.'\');"><span class="close_dialog">'.Lang::t('_CONFIRM', 'catalogue').'</span></a>')
							.'&nbsp;&nbsp;<a href="javascript:void(0);" onclick="hideDialog();"><span class="close_dialog">'.Lang::t('_UNDO', 'catalogue').'</span></a>';

		}
		else
		{
			$query =	"SELECT *"
						." FROM %lms_course"
						." WHERE idCourse = ".(int)$id_course;

			$row = sql_fetch_assoc(sql_query($query));

			$res['success'] = true;

			if($selling ==1)
				$res['title'] = Lang::t('_CONFIRM_ADD_TO_CART', 'catalogue');
			else
				$res['title'] = Lang::t('_CONFIRM_SUBSCRIPTION', 'catalogue');

			$res['body'] =	Lang::t('_NAME', 'catalogue').': '.$row['name'].'<br/>'
							.($row['code'] !== '' ? Lang::t('_CODE', 'catalogue').': '.$row['code'].'<br/>' : '')
							.($row['date_begin'] !== '0000-00-00' ? Lang::t('_DATE_BEGIN', 'course').': '.Format::date($row['date_begin'], 'date').'<br/>' : '')
							.($row['date_end'] !== '0000-00-00' ? Lang::t('_DATE_END', 'course').': '.Format::date($row['date_end'], 'date').'<br/>' : '');
							//.'<a href="javascript:void(0);" onclick="hideDialog();"><p class="close_dialog">'.Lang::t('UNDO', 'catalogue').'</p></a>';
			$res['footer'] = ($selling == 1 ? '<a href="javascript:void(0);" onclick="subscribeToCourse(\''.$id_course.'\', \''.$id_date.'\', \''.$id_edition.'\', \''.$selling.'\');"><span class="confirm_dialog">'.Lang::t('_CONFIRM', 'catalogue').' ('.$row['prize'].' '.Get::sett('currency_symbol', '&euro;').')'.'</span></a>'
											: '<a href="javascript:void(0);" onclick="subscribeToCourse(\''.$id_course.'\', \''.$id_date.'\', \''.$id_edition.'\', \''.$selling.'\');"><span class="confirm_dialog">'.Lang::t('_CONFIRM', 'catalogue').'</span></a>')
					.'&nbsp;&nbsp;<a href="javascript:void(0);" onclick="hideDialog();"><span class="close_dialog">'.Lang::t('_UNDO', 'catalogue').'</span></a>';
		}

		return $res;
	}

	public function courseSelectionInfo($id_course, $selling)
	{
		$query =	"SELECT course_type"
					." FROM %lms_course"
					." WHERE idCourse = ".(int)$id_course;

		list($type) = sql_fetch_row(sql_query($query));
		$res['success'] = true;

		if($selling ==1)
			$res['title'] = Lang::t('_ADD_TO_CHART', 'catalogue');
		else
			$res['title'] = Lang::t('_SUBSCRIBE', 'catalogue');

		$res['body'] = '';

		if($type === 'classroom')
		{
			$classrooms = $this->classroom_man->getCourseDate($id_course, false);

			$user_classroom = $this->classroom_man->getUserDates(Docebo::user()->getIdSt());
			$classroom_full = $this->classroom_man->getFullDateForCourse($id_course);
			$classroom_not_confirmed = $this->classroom_man->getNotConfirmetDateForCourse($id_course);

			$overbooking_classroom = $this->classroom_man->getOverbookingDateForCourse($id_course);

			foreach($classrooms as $classroom_info)
			{
				if(isset($user_classroom[$classroom_info['id_date']]))
					$action = '<p class="subscribed">'.Lang::t('_USER_STATUS_SUBS', 'catalogue').'</p>';
				elseif(isset($_SESSION[$id_course]['classroom'][$classroom_info['id_date']]))
					$action = '<p class="subscribed">'.Lang::t('_CLASSROOM_IN_CART', 'catalogue').'</p>';
				elseif(isset($classroom_full[$classroom_info['id_date']])) {
					if (isset($overbooking_classroom[$classroom_info['id_date']])) {
						$action = '<a href="javascript:void(0);" onclick="subscriptionPopUp(\''.$id_course.'\', \''.$classroom_info['id_date'].'\', \'0\', \''.$selling.'\');"><span class="can_subscribe">'.Lang::t('_SUBSCRIBE_WITH_OVERBOOKING', 'catalogue').'</span></a>';
					} else {
						$action = '<p class="subscribed">'.Lang::t('_CLASSROOM_FULL', 'catalogue').'</p>';
					}
				} else
					$action = ($selling == 1	? '<a href="javascript:void(0);" onclick="subscriptionPopUp(\''.$id_course.'\', \''.$classroom_info['id_date'].'\', \'0\', \''.$selling.'\');"><span class="can_subscribe">'.Lang::t('_ADD_TO_CART', 'catalogue').' ('.$classroom_info['price'].' '.Get::sett('currency_symbol', '&euro;').')'.'</span></a>'
												: '<a href="javascript:void(0);" onclick="subscriptionPopUp(\''.$id_course.'\', \''.$classroom_info['id_date'].'\', \'0\', \''.$selling.'\');"><span class="can_subscribe">'.Lang::t('_SUBSCRIBE', 'catalogue').'</span></a>');

				$res['body'] .=	'<div class="edition_container">'
								.'<b>'.Lang::t('_NAME', 'catalogue').'</b>: '.$classroom_info['name'].'<br/>'
								.($classroom_info['code'] !== '' ? '<b>'.Lang::t('_CODE', 'catalogue').'</b>: '.$classroom_info['code'].'<br/>' : '')
								.($classroom_info['date_begin'] !== '0000-00-00 00:00:00' ? '<b>'.Lang::t('_DATE_BEGIN', 'course').'</b>: '.Format::date($classroom_info['date_begin'], 'datetime').'<br/>' : '')
								.($classroom_info['date_end'] !== '0000-00-00 00:00:00' ? '<b>'.Lang::t('_DATE_END', 'course').'</b>: '.Format::date($classroom_info['date_end'], 'datetime').'<br/>' : '')
								.($classroom_info['classroom'] !== '' ? '<b>'.Lang::t('_LOCATION', 'classroom').'</b>: '.$classroom_info['classroom'].'<br />' : '')
								.'<div class="edition_subscribe">'
								.$action
								.'</div>'
								.'</div>';
			}

			$res['footer'] = '<a href="javascript:void(0);" onclick="hideDialog();"><p class="close_dialog">'.Lang::t('_UNDO', 'catalogue').'</p></a>';
		}
		else
		{
			$edition_full = $this->edition_man->getFullEdition($id_course);
			$user_edition = $this->edition_man->getUserEdition(Docebo::user()->getIdSt());

			$editions = $this->edition_man->getEditionAvailableWithInfo(Docebo::user()->getIdSt(), $id_course);

			foreach($editions as $edition_info)
			{
				if(array_search($edition_info['id_edition'], $user_edition) !== false)
					$action = '<p class="subscribed">'.Lang::t('_USER_STATUS_SUBS', 'catalogue').'</p>';
				elseif(isset($_SESSION[$id_course]['edition'][$edition_info['id_edition']]))
					$action = '<p class="subscribed">'.Lang::t('_CLASSROOM_IN_CART', 'catalogue').'</p>';
				elseif(isset($edition_full[$edition_info['id_edition']]))
					$action = '<p class="subscribed">'.Lang::t('_CLASSROOM_FULL', 'catalogue').'</p>';
				else
					$action = ($selling == 1	? '<a href="javascript:void(0);" onclick="subscriptionPopUp(\''.$id_course.'\', \'0\', \''.$edition_info['id_edition'].'\', \''.$selling.'\');"><span class="can_subscribe">'.Lang::t('_ADD_TO_CART', 'catalogue').' ('.$edition_info['price'].' '.Get::sett('currency_symbol', '&euro;').')'.'</span></a>'
												: '<a href="javascript:void(0);" onclick="subscriptionPopUp(\''.$id_course.'\', \'0\', \''.$edition_info['id_edition'].'\', \''.$selling.'\');"><span class="can_subscribe">'.Lang::t('_SUBSCRIBE', 'catalogue').'</span></a>');

				$res['body'] .=	'<div class="edition_container">'
								.'<b>'.Lang::t('_NAME', 'catalogue').'</b>: '.$edition_info['name'].'<br/>'
								.($edition_info['code'] !== '' ? '<b>'.Lang::t('_CODE', 'catalogue').'</b>: '.$edition_info['code'].'<br/>' : '')
								.($edition_info['date_begin'] !== '0000-00-00 00:00:00' ? '<b>'.Lang::t('_DATE_BEGIN', 'course').'</b>: '.Format::date($edition_info['date_begin'], 'date').'<br/>' : '')
								.($edition_info['date_end'] !== '0000-00-00 00:00:00' ? '<b>'.Lang::t('_DATE_END', 'course').'</b>: '.Format::date($edition_info['date_end'], 'date').'<br/>' : '')
								.'<div class="edition_subscribe">'
								.$action
								.'</div>'
								.'</div>';
			}

			$res['footer'] = '<div class="edition_cancel"><a href="javascript:void(0);" onclick="hideDialog();"><span class="close_dialog">'.Lang::t('_UNDO', 'catalogue').'</span></a></div>';
		}

		return $res;
	}

	public function controlSubscriptionRemaining($id_course)
	{
		$query =	"SELECT *"
					." FROM %lms_course"
					." WHERE idCourse = ".(int)$id_course;

		$result = sql_query($query);

		$row = sql_fetch_assoc($result);
		if($row['course_type'] === 'classroom')
		{
			$additional_info = '';

			$classrooms = $this->classroom_man->getCourseDate($row['idCourse'], false);

			if(count($classrooms) == 0)
				return false;
			else
			{
				//Controllo che l'utente non sia iscritto a tutte le edizioni future
				$date_id = array();

				$user_classroom = $this->classroom_man->getUserDates(Docebo::user()->getIdSt());
				$classroom_full = $this->classroom_man->getFullDateForCourse($row['idCourse']);
				$classroom_not_confirmed = $this->classroom_man->getNotConfirmetDateForCourse($row['idCourse']);

				foreach($classrooms as $classroom_info)
					$date_id[] = $classroom_info['id_date'];

				reset($classrooms);

				$control = array_diff($date_id, $user_classroom, $classroom_full, $classroom_not_confirmed);

				if(count($control) == 0)
					return false;
				else
				{
					if($row['selling'] == 0)
						return true;
					else
					{
						$classroom_in_chart = array();

						if(isset($_SESSION['lms_cart'][$row['idCourse']]['classroom']))
							$classroom_in_chart = $_SESSION['lms_cart'][$row['idCourse']]['classroom'];

						$control = array_diff($control, $classroom_in_chart);

						if(count($control) == 0)
							return false;
						else
							return true;
					}
				}
			}
		}
		elseif($row['course_edition'] == 1)
		{
			$additional_info = '';

			$editions = $this->edition_man->getEditionAvailableForCourse(Docebo::user()->getIdSt(), $row['idCourse']);

			if(count($editions) == 0)
				return false;
			else
			{
				if($row['selling'] == 0)
					return true;
				else
				{
					$edition_in_chart = array();

					if(isset($_SESSION['lms_cart'][$row['idCourse']]['editions']))
						$edition_in_chart = $_SESSION['lms_cart'][$row['idCourse']]['editions'];

					$editions = array_diff($editions, $edition_in_chart);

					if(count($editions) == 0)
						return false;
					else
						return true;
				}
			}
		}
	}

	public function getMajorCategory($std_link, $only_son = false)
	{
		$query =	"SELECT idCategory, path, lev"
					." FROM %lms_category"
					." WHERE lev = 1"
					." ORDER BY path";

		$result = sql_query($query);
		$res = array();

		while(list($id_cat, $path, $level) = sql_fetch_row($result))
		{
			$name = end(explode('/', $path));
			$res[$id_cat] = $name;
		}

		return $res;
	}

	public function getMinorCategory($std_link)
	{
		$query =	"SELECT idCategory, path, idParent, lev"
					." FROM %lms_category";

		$id_cat = Get::req('id_cat', DOTY_INT, 0);
		$level = 1;

		if($id_cat > 0)
		{
			$query_i =	"SELECT iLeft, iRight, lev"
						." FROM %lms_category"
						." WHERE idCategory = ".(int)$id_cat;

			list($i_left, $i_right, $lev) = sql_fetch_row(sql_query($query_i));

			$level = $lev + 1;

			$query .=	" WHERE iLeft > ".(int)$i_left
						." AND iRight < ".$i_right;
		}
		else
			$query .=	" WHERE lev IN (".$level.", ".($level + 1).")";

		$query .= " ORDER BY path";

		$result = sql_query($query);
		$res = array();

		while(list($id_cat, $path, $id_parent, $lev) = sql_fetch_row($result))
		{
			$name = end(explode('/', $path));

			if($level == $lev)
				$res[$id_cat]['name'] = $name;
			else
				$res[$id_parent]['son'][$id_cat] = $name;
		}

		return $res;
	}

	public function getBreadCrumbs($std_link)
	{
		$id_cat = Get::req('id_cat', DOTY_INT, 0);
		$res = '<ul class="navigation">';

		if($id_cat > 0)
		{
			$res .= '<li><a href="'.$std_link.'">'.Lang::t('_ALL_CATEGORIES', 'catalog').'</a></li>';

			$query =	"SELECT iLeft, iRight, path"
						." FROM %lms_category"
						."  WHERE idCategory = ".(int)$id_cat;

			list($i_left, $i_right, $path_t) = sql_fetch_row(sql_query($query));

			$query =	"SELECT idCategory, path"
						." FROM %lms_category"
						." WHERE iLeft < ".(int)$i_left
						." AND iRight > ".(int)$i_right
						." ORDER BY path";

			$result = sql_query($query);

			while(list($id_cat_t, $path) = sql_fetch_row($result))
			{
				$name = end(explode('/', $path));
				$res .= ' > <li><a href="'.$std_link.'&amp;id_cat='.$id_cat_t.'">'.$name.'</a></li>';
			}

			$name = end(explode('/', $path_t));

			$res .= ' > <li>'.$name.'</li>';
		}
		else
		{
			$res .= '<li>'.Lang::t('_ALL_CATEGORIES', 'catalog').'</li>';
		}

		$res .= '</ul>';

		return $res;
	}
    
    
    
    
        public function GetGlobalJsonTree(){
            $global_tree = [];
            $top_category = $this->getMajorCategory();
            foreach ($top_category as $a_top_cat_key=>$val) {
                $this->tree_deep = 0;
                $this->the_tree = [];
                $this->children = $this->getMinorCategoryTree($a_top_cat_key);
                $this->GetChildTree(array_keys($this->children));
                if (count($this->the_tree)>0) {
                    $global_tree[] = array('text'=>$this->the_tree[0]['text'], 'nodes'=>$this->the_tree[0]['nodes'], "href" => "index.php?r=catalog/allCourse&id_cat=".$a_top_cat_key, "id_cat" => $a_top_cat_key);
                } else {
                    $global_tree[] = array('text'=>$val, "href" => "index.php?r=catalog/allCourse&id_cat=".$a_top_cat_key, "id_cat" => $a_top_cat_key);
                }    
            }
            return  $global_tree;              
            
              
        }

        public function getMinorCategoryTree($id_cat){
            $query_i =    "SELECT iLeft, iRight, idCategory"
            ." FROM %lms_category"
            ." WHERE idCategory = ".(int)$id_cat;
            list($i_left, $i_right) = sql_fetch_row(sql_query($query_i));

            $query =    "SELECT idCategory, path, idParent, lev"
            ." FROM %lms_category"
            ." WHERE iLeft >= ".(int)$i_left
            ." AND iRight <= ".$i_right
            ." ORDER BY lev";
            $result = sql_query($query);
            $res = array();

            while(list($id_cat, $path, $id_parent, $lev) = sql_fetch_row($result)){
                $name = end(explode('/', $path));

                $res[$id_cat]['name'] = $name;
                $res[$id_cat]['id_cat'] = $id_cat;
                if($id_parent != 0) {
                    $res[$id_parent]['son'][$id_cat] = $name;
                }    
            }
            return $res;            

        }        

        private function GetChildTree($array_k){
            $leaves = [];       
            foreach ($array_k as $single_key) {
                if (is_array($this->children[$single_key]['son'])) {
                    $this->tree_deep++;
                    $b = $this->GetChildTree(array_keys($this->children[$single_key]['son']));
                    
                    $leaves[] = array('text'=>$this->children[$single_key]['name'], 'nodes'=>$b, "href" => "index.php?r=catalog/allCourse&id_cat=".$this->children[$single_key]['id_cat'],"id_cat" => $this->children[$single_key]['id_cat'] );
                    if  ($this->tree_deep==0){
                        $this->the_tree[] = array('text'=>$leaves[0]['text'], 'nodes'=>$leaves[0]['nodes'], "href" => "index.php?r=catalog/allCourse&id_cat=".$this->children[$single_key]['id_cat'],"id_cat" => $this->children[$single_key]['id_cat'] );
                        $this->tree_deep = 0;                   
                    }
                } else {
                    $leaves[] = array('text'=>$this->children[$single_key]['name'],  "href" => "index.php?r=catalog/allCourse&id_cat=".$this->children[$single_key]['id_cat'],"id_cat" => $this->children[$single_key]['id_cat']);
                }
                if (array_key_exists($single_key, $this->children)) {
                    unset($this->children[$single_key]);   
                }
            }
            
            
            $this->tree_deep--;
            return $leaves;
        }     
    
    
    
    
    
}

?>