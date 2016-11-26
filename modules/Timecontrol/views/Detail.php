<?php
/************************************************************************************
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
 * *********************************************************************************** */

class Timecontrol_Detail_View extends Vtiger_Detail_View {

	public function process(Vtiger_Request $request) {
		global $current_user;
		$viewer = $this->getViewer($request);
		$recordModel = $this->record->getRecord();
		if ($recordModel->get('date_end')=='') {
		  $date = $recordModel->get('date_start');
		  $time = $recordModel->get('time_start');
		  list($year, $month, $day) = split('-', $date);
		  list($hour, $minute) = split(':', $time);
		  $starttime = mktime($hour, $minute, 0, $month, $day, $year);
		  // las sgtes líneas deberían bastar para calcular el tiempo en función de la zona horario del usuario
// 		  $datetimefield = new DateTimeField('');
// 		  list($year, $month, $day) = split('-', $datetimefield->getDBInsertDateValue($current_user));
// 		  list($hour, $minute) = split(':', $datetimefield->getDBInsertTimeValue($current_user));
// 		  $nowtime = mktime($hour, $minute, 0, $month, $day, $year);
		  $nowtime = time();
		  $counter = $nowtime-$starttime;
		  $viewer->assign('SHOW_WATCH', 'started');
		  $viewer->assign('WATCH_COUNTER', $counter);
		}
		else {
		  $viewer->assign('SHOW_WATCH', 'halted');
		  $viewer->assign('WATCH_DISPLAY', $recordModel->get('totaltime'));
		}
		parent::process($request);
	}
}
