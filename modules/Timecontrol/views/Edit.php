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

vimport('modules.Timecontrol.Timecontrol');

class Timecontrol_Edit_View extends Vtiger_Edit_View {

	public function process(Vtiger_Request $request) {
		global $current_user,$adb;
		
		if ($request->has('stop_watch') and $request->get('stop_watch')==0) {
			$vtnow=new DateTimeField(null);
			$request->set('time_start', $vtnow->getDisplayTime($current_user));
			$request->set('time_end', '');
			if (Timecontrol::$now_on_resume) {
				$request->set('date_start', $vtnow->getDisplayDate($current_user));
				$request->set('date_end', $vtnow->getDisplayDate($current_user));
			}
			$request->set('tcunits', 1);
			$request->set('totaltime', '');
		}
		
		if(!$request->has('record') && $request->get('mode') != 'edit'){
			$vtnow=new DateTimeField(null);
			$request->set('time_start', $vtnow->getDisplayTime($current_user));
			$request->set('date_start', $vtnow->getDisplayDate($current_user));
			$request->set('date_end', $vtnow->getDisplayDate($current_user));
			$request->set('tcunits', 1);
			$rshd=$adb->pquery('select tcproduct from vtiger_users where id=?',array($current_user->id));
			if ($rshd) {
				$tcpdo = $adb->query_result($rshd,0,'tcproduct');
				if (!empty($tcpdo)) {
					$request->set('product_id',$tcpdo);
				}
			}
		}
		
		// Contribution made by Ted Janzen of Janzen & Janzen ICT Services http://www.j2ict.nl
		if ($request->has('relatedto') and getSalesEntityType($request->get('relatedto'))=='HelpDesk') { // coming from TT, pickup data
			$rshd=$adb->pquery('select ticket_no,product_id from vtiger_troubletickets where ticketid=?',array($request->get('relatedto')));
			if ($rshd) {
				if (!$request->has('product_id') or $request->isEmpty('product_id')) {
					$request->set('product_id',$adb->query_result($rshd,0,'product_id'));
				}
				$request->set('title',$adb->query_result($rshd,0,'ticket_no'));
			}
		}
		
		parent::process($request);
	}
}
