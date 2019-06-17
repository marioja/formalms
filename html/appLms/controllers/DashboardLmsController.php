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

/**
 * Class DashboardLmsController
 */
class DashboardLmsController extends LmsController
{

	public $name = '';

	/** @var DashboardLms */
	private $model;

	/**
	 * DashboardLmsController constructor.
	 * @param $mvc_name
	 */
	public function __construct($mvc_name)
	{
		parent::__construct($mvc_name);

		$this->model = new DashboardLms();
	}

	public function show()
	{
		$blocks = $this->model->getBlocksViewData();

		$this->render('dashboard', [
			'blocks' => $blocks
		]);
	}
}
