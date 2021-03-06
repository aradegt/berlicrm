<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

// if display_errors is enabled, suppress notices and warnings for ajax calls to not break JSON responses
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

class Vtiger_BasicAjax_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$searchValue = $request->get('search_value');
		$searchModule = $request->get('search_module');

		$parentRecordId = $request->get('parent_id');
		$parentModuleName = $request->get('parent_module');
		$relatedModule = $request->get('module');

        // get results for autocomplete fields (uitype cr16)
        if ($searchModule == "Picklist") {
            $fieldname = $request->get('fieldname');
            // sanitize fieldname for use as tablename
            $ftmp = explode("_",$fieldname);
            if ($ftmp[0]!="cf" || !is_numeric($ftmp[1])) {
                throw new AppException(vtranslate('LBL_NO_RECORDS_FOUND'));
            }
            global $adb;
            $q = "SELECT $fieldname as value FROM vtiger_$fieldname WHERE presence = 1 AND $fieldname LIKE ? ORDER BY sortorderid";
            $res = $adb->pquery($q,array($searchValue."%"));
            while ($res && $row=$adb->fetchByAssoc($res,-1,false)) {
                $result[] = array('value'=>$row['value']);
            }
        }
        else {
            $searchModuleModel = Vtiger_Module_Model::getInstance($searchModule);

            $records = $searchModuleModel->searchRecord($searchValue, $parentRecordId, $parentModuleName, $relatedModule);

            $result = array();
            if(is_array($records)){
                foreach($records as $moduleName=>$recordModels) {
                    foreach($recordModels as $recordModel) {
                        $result[] = array('label'=>decode_html($recordModel->getName()), 'value'=>decode_html($recordModel->getName()), 'id'=>$recordModel->getId());
                    }
                }
            }
        }
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
