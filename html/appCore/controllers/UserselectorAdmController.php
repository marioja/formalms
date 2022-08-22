<?php

use FormaLms\lib\Selectors\Multiuserselector\MultiUserSelector;

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


class UserselectorAdmController extends AdmController
{
    protected $multiUserSelector;

    protected $tabFilters =  array();

    protected $requestObj;

    protected $selection = 'user';

    protected $tabs = ['user' => false,
                        'group' => false,
                        'org' => false,
                        'role' => false];

    public function init(){

        $this->multiUserSelector = new MultiUserSelector();
        $this->_mvc_name = 'multiuserselector';
        $this->requestObj = $this->request->getMethod() == "POST" ? $this->request->request : $this->request->query;
        
        $tabs = ($this->requestObj->has('tab_filters')) ? $this->requestObj->get('tab_filters') : array_keys($this->tabs);

        foreach($tabs as $tabFilter) {

            if(!in_array($tabFilter, array_keys($this->tabs))) {
                //non è un filtro accettato lo skippo
                continue;
            }
            $this->tabs[$tabFilter] = true;
            $dataSelectorName = ucfirst($tabFilter) . 'DataSelector';
    
            
            $this->multiUserSelector->setDataSelectors($dataSelectorName, $tabFilter);
        }

       
        return $this;
    }

  

    public function show() {

        $disableAjax = $this->requestObj->has('disable_ajax') ? true : false;
        $instanceValue = $this->requestObj->get('instance');
        $instanceId = $this->requestObj->get('id');
        $orgChart = null;
        if($this->requestObj->has('selected_tab') && in_array($this->requestObj->get('selected_tab'), array_keys($this->tabs))) {
            $this->selection = $this->requestObj->get('selected_tab');
        }

       // $this->multiUserSelector->setDataSelectors('DataSelector', 'user');
       // $requestParams = ($this->requestObj->has('params')) ? $this->requestObj->get('params') : [];
        //$requestParams['op'] = 'selectall';
        $selectedData = []; //$this->multiUserSelector->retrieveDataSelector($this->selection)->getData($requestParams);
        //dd($selectedData);

        foreach($this->tabs as $tabKey => $tab) {
            $multiUserSelectorTab = $this->multiUserSelector->retrieveDataSelector($tabKey);
           
            $columns[$tabKey] = $multiUserSelectorTab->getColumns();
            if(count($multiUserSelectorTab::ADDITIONAL_COLS)) {
                $hiddenColumns = $multiUserSelectorTab->getHiddenColumns();
                $columns[$tabKey] = array_merge($columns[$tabKey], $hiddenColumns);
            }

            if($disableAjax) {
                $requestParams['op'] = 'selectall';
                $selectedData[$tabKey] = $multiUserSelectorTab->getData($requestParams);
            }
        }

        if($this->tabs['org']) {
            $orgChart = $this->multiUserSelector->retrieveDataSelector('org')->getChart();
        }

        $this->render('show',['tabs' => $this->tabs,
                            'selection'=> $this->selection,
                            'columns' => $columns,
                            'orgChart' => $orgChart,
                            'ajax' => $disableAjax,
                            'selectedData' => $selectedData,
                            'instanceValue' => $instanceValue,
                            'instanceId' => $instanceId,
                            'debug' => $this->requestObj->has('debug') ? $this->requestObj->get('debug') : false
                        ]);
    }


    public function getDataTask()
    {
        
        $dataType = $this->requestObj->get('dataType');
        $params = array_merge($this->requestObj->all(), ['json_format' => true]);

        switch($dataType) {
            case "user":
            case "group":
            case "role":
                $response = $this->multiUserSelector->retrieveDataselector($dataType)->getData($params);
               
               break;
            default:
                $params = $this->request->query->all();
                $response = $this->multiUserSelector->retrieveDataselector('org')->getData($params);
                break;
        }

        echo $response;

    }


    public function testPostTask()
    {
        
        dd($this->requestObj);

    }

    public function associate()
    {
        
        $instanceType = $this->requestObj->get('instance');
        $instanceId = (int) $this->requestObj->get('id');

        $selection =  explode(',', $this->requestObj->get('selected'));
        
        $this->multiUserSelector->injectAccessModel($instanceType);

        $accessModel = $this->multiUserSelector->getAccessModel();
        switch($instanceType) {

            case 'communication':
               
                $oldSelection = $accessModel->accessList($instanceId);
                
                if ($accessModel->updateAccessList($instanceId, $oldSelection, $selection)) {
                    Util::jump_to('index.php?r=alms/communication/show&success=1');
                } else {
                    Util::jump_to('index.php?r=alms/communication/show&error=1');
                }
               
                break;
        }

    }


}
