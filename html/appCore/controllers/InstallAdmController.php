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

defined('IN_FORMA') or exit('Direct access is forbidden.');

class InstallAdmController extends AdmController
{

    protected InstallAdm $model;
  
    public function init()
    {
        $debug =  $this->request->query->has('debug') ? (int) $this->request->query->get('debug') : 0;

        $this->model = new InstallAdm($debug);
    }

    public function show()
    {
      
        $params = $this->model->getData($this->request);

        $params['steps'] = $this->model->getSteps();
        $params['languages'] = Lang::getFileSystemCoreLanguages('language');
        $params['setLang'] = Lang::getSelLang();

        $this->render('show', $params);

    }

    public function set() {

        $result = json_encode(array('success' => $this->model->setValue($this->request)));
        echo $result;
      
        exit;
    }

    public function checkDbData() {

        echo $this->model->checkDbData($this->request);
        exit;
    }

    public function testMigrations() {

        $params = $this->request->request->all();

        if((int) $params['upgrade']) {
            $this->model->installMigrationsTable();
        }
        $writeSqlFile = dirname(__DIR__, 2) . "/files/migration" . floor(microtime(true) * 1000) .".sql";

        $testLine = '--dry-run --write-sql=' . $writeSqlFile;
        $messages[] = $this->model->migrate((bool) $params['debug'], $testLine);
        $messages[] = 'CHECK: ' . $writeSqlFile;
       
        echo $this->model->setResponse(true, $messages)->wrapResponse();
      
    }

    public function getErrorMessages() {

        $result = json_encode(array('messages' =>  $this->model->getErrorMessages($this->request)));
        echo $result;
        exit;
       
    }

    public function checkFtp() {

        echo $this->model->checkFtp($this->request);
       
        exit;
       
    }


    public function checkAdminData() {
        echo $this->model->checkAdminData($this->request);
       
        exit;
    }

    public function checkSmtpData() {
        echo $this->model->checkSmtpData($this->request);
       
        exit;
    }

    public function finalize() {
        echo $this->model->finalize($this->request);
       
        exit;
    }

    public function formSave() {
        echo $this->model->saveFields($this->request);
       
        exit;
    }

 
}
