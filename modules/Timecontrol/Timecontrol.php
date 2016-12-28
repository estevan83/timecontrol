<?php

/* * *********************************************************************************
 * Copyright 2014 JPL TSolucio, S.L.  --  This file is a part of vtiger CRM TimeControl extension.
 * You can copy, adapt and distribute the work under the "Attribution-NonCommercial-ShareAlike"
 * Vizsage Public License (the "License"). You may not use this file except in compliance with the
 * License. Roughly speaking, non-commercial users may share and modify this code, but must give credit
 * and share improvements. However, for proper details please read the full License, available at
 * http://vizsage.com/license/Vizsage-License-BY-NC-SA.html and the handy reference for understanding
 * the full license at http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html. Unless required by
 * applicable law or agreed to in writing, any software distributed under the License is distributed
 * on an  "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the
 * License terms of Creative Commons Attribution-NonCommercial-ShareAlike 3.0 (the License).
 * ********************************************************************************** */

include_once 'modules/Vtiger/CRMEntity.php';

class Timecontrol extends Vtiger_CRMEntity {

    // Variable to esablish start value on resume
    // true: dates and start time will be set to "now"
    // false: only start time will be set to "now"
    public static $now_on_resume = true;
    var $USE_RTE = 'true';
    var $sumup_HelpDesk = true;
    var $sumup_ProjectTask = true;
    var $table_name = 'vtiger_timecontrol';
    var $table_index = 'timecontrolid';

    /**
     * Mandatory table for supporting custom fields.
     */
    var $customFieldTable = Array('vtiger_timecontrolcf', 'timecontrolid');
    var $related_tables = Array('vtiger_timecontrolcf' => array('timecontrolid', 'vtiger_timecontrol', 'timecontrolid'));

    /**
     * Mandatory for Saving, Include tables related to this module.
     */
    var $tab_name = Array('vtiger_crmentity', 'vtiger_timecontrol', 'vtiger_timecontrolcf');

    /**
     * Mandatory for Saving, Include tablename and tablekey columnname here.
     */
    var $tab_name_index = Array(
        'vtiger_crmentity' => 'crmid',
        'vtiger_timecontrol' => 'timecontrolid',
        'vtiger_timecontrolcf' => 'timecontrolid');

    /**
     * Mandatory for Listing (Related listview)
     */
    var $list_fields = Array(
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'vtiger_'
        'Timecontrol Number' => array('timecontrol', 'timecontrolnr'),
        'Title' => Array('timecontrol', 'title'),
        'Date Start' => array('timecontrol', 'date_start'),
        'Time Start' => array('timecontrol', 'time_start'),
        'Total Time' => array('timecontrol', 'totaltime'),
        'Description' => Array('crmentity', 'description'),
        'Assigned To' => Array('crmentity', 'smownerid')
    );
    var $list_fields_name = Array(
        /* Format: Field Label => fieldname */
        'Timecontrol Number' => 'timecontrolnr',
        'Title' => 'title',
        'Date Start' => 'date_start',
        'Time Start' => 'time_start',
        'Total Time' => 'totaltime',
        'Description' => 'description',
        'Assigned To' => 'assigned_user_id'
    );
    // Make the field link to detail view from list view (Fieldname)
    var $list_link_field = 'timecontrolnr';
    // For Popup listview and UI type support
    var $search_fields = Array(
        /* Format: Field Label => Array(tablename, columnname) */
        // tablename should not have prefix 'vtiger_'
        'Timecontrol Number' => array('timecontrol', 'timecontrolnr'),
        'Title' => Array('timecontrol', 'title')
    );
    var $search_fields_name = Array(
        /* Format: Field Label => fieldname */
        'Timecontrol Number' => 'timecontrolnr',
        'Title' => 'title'
    );
    // For Popup window record selection
    var $popup_fields = Array('timecontrolnr');
    // For Alphabetical search
    var $def_basicsearch_col = 'timecontrolnr';
    // Column value to use on detail view record text display
    var $def_detailview_recname = 'title';
    // Used when enabling/disabling the mandatory fields for the module.
    // Refers to vtiger_field.fieldname values.
    var $mandatory_fields = Array('createdtime', 'modifiedtime', 'timecontrolnr', 'date_start', 'time_start');
    var $default_order_by = 'date_start';
    var $default_sort_order = 'DESC';

    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type
     */
    function vtlib_handler($moduleName, $eventType) {
        global $adb;
        if ($eventType == 'module.postinstall') {
            // TODO Handle actions after this module is installed.
            $this->setModuleSeqNumber('configure', $moduleName, 'TIME-BILLING-', '000001');
            self::addTSRelations();
            self::setSummaryFields();
        } else if ($eventType == 'module.disabled') {
            // TODO Handle actions before this module is being uninstalled.
        } else if ($eventType == 'module.preuninstall') {
            // TODO Handle actions when this module is about to be deleted.
        } else if ($eventType == 'module.preupdate') {
            // TODO Handle actions before this module is updated.
        } else if ($eventType == 'module.postupdate') {
            // TODO Handle actions after this module is updated.
        }
    }

    static function addTSRelations($dorel = true) {
        $Vtiger_Utils_Log = true;
        include_once('vtlib/Vtiger/Module.php');

        $module = Vtiger_Module::getInstance('Timecontrol');

        $cfgTCMods = array('Vendors', 'Assets', 'ProjectTask', 'ProjectMilestone', 'Project', 'Leads', 'Accounts', 'Contacts',
            'Campaigns', 'Potentials', 'Invoice', 'PurchaseOrder', 'SalesOrder', 'Quotes', 'HelpDesk', 'Services', 'Products',
            'ServiceContracts');
        foreach ($cfgTCMods as $tcmod) {
            $rtcModule = Vtiger_Module::getInstance($tcmod);
            $rtcModule->setRelatedList($module, 'Timecontrol', Array('ADD'), 'get_dependents_list');
            $rtcModule->addLink('DETAILVIEWBASIC', 'Timecontrol', 'index.php?module=Timecontrol&action=EditView&relatedto=$RECORD$', 'modules/Timecontrol/images/stopwatch.gif');
        }
    }

    static function setSummaryFields() {
        global $adb;
        $Vtiger_Utils_Log = true;
        include_once('vtlib/Vtiger/Module.php');
        $module = Vtiger_Module::getInstance('Timecontrol');
        $sumfields = array('timecontrolnr', 'totaltime', 'relatedto', 'date_end', 'time_end', 'date_start', 'time_start', 'title');
        foreach ($sumfields as $fldname) {
            $fld = Vtiger_Field::getInstance($fldname, $module);
            $adb->query('update vtiger_field set summaryfield=1 where fieldid=' . $fld->id);
        }
    }

    function save_module($module) {
        global $log;
        $this->updateTimesheetTotalTime();
        $this->updateRelatedEntities($this->id);
        if (!empty($this->column_fields['relatedto'])) {
            $relmod = getSalesEntityType($this->column_fields['relatedto']);
            $seqfld = $this->getModuleSequenceField($relmod);
            $relm = CRMEntity::getInstance($relmod);
            $relm->retrieve_entity_info($this->column_fields['relatedto'], $relmod);
            $enum = $relm->column_fields[$seqfld['column']];
            $ename = getEntityName($relmod, array($this->column_fields['relatedto']));
            $ename = decode_html($ename[$this->column_fields['relatedto']]);
            $this->db->query("update vtiger_timecontrol set relatednum='$enum', relatedname='$ename' where timecontrolid=" . $this->id);
        }
    }

    /**     Update totaltime field   */
    function updateTimesheetTotalTime() {
        global $adb;
        if (!empty($this->column_fields['date_end']) && !empty($this->column_fields['time_end'])) {
            $query = "select date_start, time_start, date_end, time_end from vtiger_timecontrol where timecontrolid={$this->id}";
            $res = $adb->query($query);
            $date = $adb->query_result($res, 0, 'date_start');
            $time = $adb->query_result($res, 0, 'time_start');
            list($year, $month, $day) = explode('-', $date);
            list($hour, $minute) = explode(':', $time);
            $starttime = mktime($hour, $minute, 0, $month, $day, $year);
            $date = $adb->query_result($res, 0, 'date_end');
            $time = $adb->query_result($res, 0, 'time_end');
            list($year, $month, $day) = explode('-', $date);
            list($hour, $minute) = explode(':', $time);
            $endtime = mktime($hour, $minute, 0, $month, $day, $year);
            $counter = round(($endtime - $starttime) / 60);
            $totaltime = str_pad(floor($counter / 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad($counter % 60, 2, '0', STR_PAD_LEFT);
            $query = "update vtiger_timecontrol set totaltime='{$totaltime}' where timecontrolid={$this->id}";
            $adb->query($query);
            self::update_totalday_control($this->id);
        }
        if (!empty($this->column_fields['totaltime']) && (empty($this->column_fields['date_end']) && empty($this->column_fields['time_end']))) {
            $totaltime = $this->column_fields['totaltime'];
            if (strpos($this->column_fields['totaltime'], ':')) { // tenemos formato h:m:s, lo paso a minutos
                $tt = explode(':', $this->column_fields['totaltime']);
                $this->column_fields['totaltime'] = $tt[0] * 60 + $tt[1];
            }
            $query = "select date_start, time_start, date_end, time_end from vtiger_timecontrol where timecontrolid={$this->id}";
            $res = $adb->query($query);
            $date = $adb->query_result($res, 0, 'date_start');
            $time = $adb->query_result($res, 0, 'time_start');
            list($year, $month, $day) = explode('-', $date);
            list($hour, $minute, $seconds) = explode(':', $time);
            $endtime = mktime($hour, $minute + $this->column_fields['totaltime'], $seconds, $month, $day, $year);
            $datetimefield = new DateTimeField(date('Y-m-d', $endtime));
            $this->column_fields['date_end'] = $datetimefield->getDisplayDate();
            $this->column_fields['time_end'] = date('H:i:s', $endtime);
            $query = "update vtiger_timecontrol set totaltime='{$totaltime}', date_end='" . date('Y-m-d', $endtime) . "', time_end='{$this->column_fields['time_end']}' where timecontrolid={$this->id}";
            $adb->query($query);
            self::update_totalday_control($this->id);
        }
    }

    public static function update_totalday_control($tcid) {
        global $adb, $log;
        if (self::totalday_control_installed()) {
            $tcdat = $adb->query("select date_start, smownerid
					from vtiger_timecontrol
					inner join vtiger_crmentity on crmid=timecontrolid
					where crmid=" . $tcid);
            $workdate = $adb->query_result($tcdat, 0, 'date_start');
            $user = $adb->query_result($tcdat, 0, 'smownerid');
            $tctot = $adb->query("select coalesce(sum(time_to_sec(totaltime))/3600,0) as totnum, coalesce(sec_to_time(sum(time_to_sec(totaltime))),0) as tottime
					from vtiger_timecontrol
					inner join vtiger_crmentity on crmid=timecontrolid
					where date_start='$workdate' and smownerid=$user and deleted=0");
            $totnum = $adb->query_result($tctot, 0, 'totnum');
            $tottim = $adb->query_result($tctot, 0, 'tottime');
            $adb->query("update vtiger_timecontrol
					inner join vtiger_crmentity on crmid=timecontrolid
					set totaldayhours=$totnum,totaldaytime='$tottim'
					where date_start='$workdate' and smownerid=$user");
        }
    }

    public static function totalday_control_installed() {
        global $adb;
        $cnacc = $adb->getColumnNames('vtiger_timecontrol');
        if (in_array('totaldaytime', $cnacc)
                and in_array('totaldayhours', $cnacc))
            return true;
        return false;
    }

    /**     Update Related Entities   */
    function updateRelatedEntities($tcid) {
        global $adb;
        $relid = $adb->query_result($adb->query("select relatedto from vtiger_timecontrol where timecontrolid=$tcid"), 0, 0);
        if (empty($relid)) {
            return true;
        }

        if ($this->sumup_HelpDesk and getSalesEntityType($relid) == 'HelpDesk') {
            $query = "select round(sum(time_to_sec(totaltime))/3600) as stt
			from vtiger_timecontrol
			inner join vtiger_crmentity on crmid=timecontrolid
			where relatedto=$relid and deleted=0";
            $res = $adb->query($query);
            $stt = $adb->query_result($res, 0, 'stt');
            $adb->pquery("update vtiger_troubletickets set hours=? where ticketid=?", array($stt, $relid));
        }
        if ($this->sumup_ProjectTask and getSalesEntityType($relid) == 'ProjectTask') {
            $query = "select sec_to_time(sum(time_to_sec(totaltime))) as stt
			from vtiger_timecontrol
			inner join vtiger_crmentity on crmid=timecontrolid
			where relatedto=$relid and deleted=0";
            $res = $adb->query($query);
            $stt = $adb->query_result($res, 0, 'stt');
            $adb->query("update vtiger_projecttask set projecttaskhours=? where projecttaskid=?", array($stt, $relid));
        }
    }

    function trash($module, $record) {
        global $adb;
        parent::trash($module, $record);
        self::update_totalday_control($record);
        $this->updateRelatedEntities($record);
        if (vtlib_isModuleActive('TCTotals')) {
            include_once 'modules/TCTotals/TCTotalsHandler.php';
            $tcdata = $adb->query("select smownerid,date_start,relatedto,product_id from vtiger_timecontrol inner join vtiger_crmentity on crmid=timecontrolid where timecontrolid=$record");
            $workdate = $adb->query_result($tcdata, 0, 'date_start');
            $tcuser = $adb->query_result($tcdata, 0, 'smownerid');
            $relto = $adb->query_result($tcdata, 0, 'relatedto');
            $pdoid = $adb->query_result($tcdata, 0, 'product_id');
            TCTotalsHandler::updateTotalTimeForUserOnDate($tcuser, $workdate);
            TCTotalsHandler::updateTotalTimeForRelatedTo($workdate, $relto, $pdoid);
        }
    }

    /* Function to get the name of the Field which is used for Module Specific Sequence Numbering, if any
     * @param module String - Module label
     * return Array - Field name and label are returned */

    private function getModuleSequenceField($module) {
        global $adb, $log;
        $log->debug("Entering function getModuleSequenceFieldName ($module)...");
        $field = null;
        if (!empty($module)) {
            //uitype 4 points to Module Numbering Field
            $seqColRes = $adb->pquery("SELECT fieldname, fieldlabel, columnname FROM vtiger_field WHERE uitype=? AND tabid=? and vtiger_field.presence in (0,2)", array('4', getTabid($module)));
            if ($adb->num_rows($seqColRes) > 0) {
                $fieldname = $adb->query_result($seqColRes, 0, 'fieldname');
                $columnname = $adb->query_result($seqColRes, 0, 'columnname');
                $fieldlabel = $adb->query_result($seqColRes, 0, 'fieldlabel');
                $field = array();
                $field['name'] = $fieldname;
                $field['column'] = $columnname;
                $field['label'] = $fieldlabel;
            }
        }
        $log->debug("Exiting getModuleSequenceFieldName...");
        return $field;
    }

    function saveentity($module, $fileid = '') {
        parent::saveentity($module, $fileid);

        if (!empty($this->column_fields['relatedto'])) {
            $relmod = getSalesEntityType($this->column_fields['relatedto']);
            $relm = CRMEntity::getInstance($relmod);
            $relm->retrieve_entity_info($this->column_fields['relatedto'], $relmod);

            $this->setRelations($relm, $relmod);
        }
    }

    protected function setRelations(CRMEntity $related, $relname) {
        $db = PearDatabase::getInstance();

        $modules = array(
            'Project' => 'linktoaccountscontacts',
            'ServiceContracts' => 'sc_related_to',
            'Assets' => 'account',
            'Contacts' => 'account_id',
            'Potentials' => 'related_to',
            'Invoice' => 'account_id',
            'PurchaseOrder' => 'account_id',
            'SalesOrder' => 'account_id',
            'Quotes' => 'account_id',
        );

        if ($relname === 'HelpDesk') {

            $cntsrv = $this->getRelatedParent($this->column_fields['relatedto'], 'HelpDesk', 'ServiceContracts');
            if (intval($cntsrv) > 0) {
                $relname = 'ServiceContracts';
                $relmod = 'ServiceContracts';
                $relm = CRMEntity::getInstance($relmod);
                $relm->retrieve_entity_info($cntsrv, $relmod);

                $result = $db->pquery("update vtiger_timecontrol set accountid = ?, servicecontractid =? where timecontrolid = ?", array($relm->column_fields['sc_related_to'], $relm->column_fields['record_id'], $this->id)
                );
                if (!$result) {
                    throw new Exception($db->database->ErrorMsg(), $db->database->ErrorNo());
                }
            }
            // è legato ad un ticket che non ha contratto di servizio
            else {

                $result = $db->pquery("update vtiger_timecontrol set accountid = ?, servicecontractid =null where timecontrolid = ?", array($related->column_fields['parent_id'], $this->id));
                if (!$result) {
                    throw new Exception($db->database->ErrorMsg(), $db->database->ErrorNo());
                }
            }
        } else if ($relname === 'ProjectTask' || $relname === 'ProjectMilestone') {
            $projectid = $related->column_fields['projectid'];
            $relmod = 'Project';
            $relm = CRMEntity::getInstance($relmod);
            $relm->retrieve_entity_info($projectid, $relmod);

            $result = $db->pquery("update vtiger_timecontrol set accountid = ?, projectid = ? where timecontrolid = ?", array($relm->column_fields['linktoaccountscontacts'], $related->column_fields['projectid'], $this->id));
            if (!$result) {
                throw new Exception($db->database->ErrorMsg(), $db->database->ErrorNo());
            }
        }

        //
        else if ($relname === 'Project') {
            $result = $db->pquery("update vtiger_timecontrol set accountid = ? where timecontrolid = ?", array($related->column_fields['linktoaccountscontacts'], $this->id));
            if (!$result) {
                throw new Exception($db->database->ErrorMsg(), $db->database->ErrorNo());
            }
        } else if (array_key_exists($relname, $modules)) {
            $field = $modules[$relname];
            $result = $db->pquery("update vtiger_timecontrol set accountid = ? where timecontrolid = ?", array($related->column_fields[$field], $this->id));
            if (!$result) {
                throw new Exception($db->database->ErrorMsg(), $db->database->ErrorNo());
            }
        }

        // Update relname
        $result = $db->pquery("update vtiger_timecontrol set reltype=? where timecontrolid = ?", array($relname, $this->id));
        if (!$result) {
            throw new Exception($db->database->ErrorMsg(), $db->database->ErrorNo());
        }
    }

    protected function getRelatedParent($chidId, $childType, $parentType) {
        $db = PearDatabase::getInstance();
        $result = $db->pquery("
            select id from
            (
                select relcrmid as id  from vtiger_crmentityrel  where module = ? and relmodule =? and crmid=?
                union
                select crmid  as id from vtiger_crmentityrel  where relmodule =? and module =? and relcrmid=?
            )as a
            limit 0,1", array($childType, $parentType, $chidId, $childType, $parentType, $chidId)
        );
        if (!$result) {
            throw new Exception($db->database->ErrorMsg(), $db->database->ErrorNo());
        }

        $row = $db->fetchByAssoc($result);

        return $row['id'];
    }

}
