<?php
YuiLib::load(array('animation' => 'my_animation', 'container' => 'container-min', 'container' => 'container_core-min'));
cout(Util::get_js(Get::rel_path('lms') . '/views/catalog/catalog.js', true), 'page_head');

require_once(_lms_ . '/lib/lib.middlearea.php');
$ma = new Man_MiddleArea();
?>
<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		initialize("<?php echo Lang::t('_UNDO', 'standard'); ?>");
	});
</script>
<div id="global_conf" class="yui-navset yui-navset-top">
	<!--
    <ul class="yui-nav">
		<li class="<?php echo ($active_tab == 'all' ? 'selected' : ''); ?> first">
			<a href="index.php?r=catalog/allCourse">
				<em><?php echo Lang::t('_ALL_COURSES', 'catalogue') ?></em>
			</a>
		</li>
		<li class="<?php echo ($active_tab == 'new' ? 'selected' : ''); ?>">
			<a href="index.php?r=catalog/newCourse">
				<em><?php echo Lang::t('_NEW_COURSE', 'catalogue') ?></em>
			</a>
		</li>
		<li class="<?php echo ($active_tab == 'elearning' ? 'selected' : ''); ?>">
			<a href="index.php?r=catalog/elearningCourse">
				<em><?php echo Lang::t('_ELEARNING', 'catalogue') ?></em>
			</a>
		</li>
		<?php if ($ma->currentCanAccessObj('tb_classroom')) : ?>
			<li class="<?php echo ($active_tab == 'classroom' ? 'selected' : ''); ?> ">
				<a href="index.php?r=catalog/classroomCourse">
					<em><?php echo Lang::t('_CLASSROOM', 'catalogue') ?></em>
				</a>
			</li>
		<?php endif; ?>

		<?php
			foreach ($user_catalogue as $id_catalogue => $cat_info)
				echo '<li' . ($active_tab == 'catalogue_' . $id_catalogue ? ' class="selected"' : '') . '>'
				. '<a href="index.php?r=catalog/catalogueCourse&amp;id_cata=' . $id_catalogue . '">'
				. '<em>' . $cat_info['name'] . '</em>'
				. '</a>'
				. '</li>';
		?>

		<?php
			if (count($user_coursepath) > 0)
				echo '<li' . ($active_tab == 'coursepath' ? ' class="selected"' : '') . '>'
				. '<a href="index.php?r=catalog/coursepathCourse">'
				. '<em>' . Lang::t('_COURSEPATH', 'catalogue') . '</em>'
				. '</a>'
				. '</li>';
		?>
		</ul>
        -->
		<div class="yui-content"> 
		<?php
			if (!isset($_GET['r']) || $_GET['r'] !== 'catalog/coursepathCourse') {
				echo '<div class="row>'
					. '<div id="yui-main">'
                    
                    
                    
                    
					. '<div class="col-md-9">';
					//. $this->model->getBreadCrumbs($std_link);

				$category = $this->model->getMinorCategory($std_link, true);
				if(!empty($category)) {


				} else {

					echo '<br />';
				}
				
				
			}
		?>