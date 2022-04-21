<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden');

include _plugins_ . '/Dummy/Features/Events/DummyEvent.php';

class DummyLmsController extends LmsController
{
    public const mod_name = 'dummy';

    protected $model;

    public function init()
    {
        $this->model = new DummyLms();
    }

    /**
     * Return the path to the views.
     *
     * @return string
     */
    public function viewPath()
    {
        return _plugins_ . '/Dummy/Features/appLms/views';
    }

    public function show()
    {
        $params['sett'] = Forma\lib\Get::sett('dummy.foo', 'dummy');
        $this->render('show', $params);
    }

    public function render_call()
    {
        // $event = new DummyEvent();

        // \appCore\Events\DispatcherManager::dispatch(DummyEvent::EVENT_NAME, $event);

        // $params['foo']=$event->getFoo();

        $params['fop'] = 'bar';

        $this->render('render_call', $params);
    }
}
