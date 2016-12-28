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

class Timecontrol_Save_Action extends Vtiger_Save_Action {

	public function process(Vtiger_Request $request) {
            global $current_user;
            
            if ($request->get('stop_watch')) {
                    $date = new DateTimeField(null);
                    $request->set('date_end',$date->getDisplayDate($current_user));
                    $request->set('time_end',$date->getDisplayTime($current_user));
            }
            if ($request->get('date_end')=='' || $request->get('time_end')=='') {
                    $request->set('date_end','');
                    $request->set('time_end','');
            }
            parent::process($request);
	}
}
