/*************************************************************************************************
 * Copyright 2014 JPL TSolucio, S.L. -- This file is a part of TSOLUCIO vtiger CRM Customizations.
 * Licensed under the GNU General Public License (the "License"); you may not use this
 * file except in compliance with the License. You can redistribute it and/or modify it
 * under the terms of the License. JPL TSolucio, S.L. reserves all rights not expressly
 * granted by the License. vtiger CRM distributed by JPL TSolucio S.L. is distributed in
 * the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Unless required by
 * applicable law or agreed to in writing, software distributed under the License is
 * distributed on an "AS IS" BASIS, WITHOUT ANY WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing
 * permissions and limitations under the License. You may obtain a copy of the License
 * at <http://www.gnu.org/licenses/>
 *************************************************************************************************
 *  Module       : Timecontrol
 *  Version      : 6.0
 *  Author       : JPL TSolucio, S. L.
 *************************************************************************************************/

function updateClock(force) {
	var clock_counter = document.getElementById('clock_counter');
	clock_counter.value++;
	var clock_display_separator = document.getElementById('clock_display_separator');
	if (clock_counter.value % 2) {
		clock_display_separator.style.visibility = 'hidden';
	} else {
		clock_display_separator.style.visibility = 'visible';
	}
	if (clock_counter.value % 60 == 0 || force) {
		var hours = parseInt(clock_counter.value / 60 / 60);
		var minutes = parseInt(clock_counter.value / 60) % 60;
		if (hours < 10) {
			hours = '0' + hours;
		}
		if (minutes < 10) {
			minutes = '0' + minutes;
		}
		var clock_display_hours = document.getElementById('clock_display_hours');
		var clock_display_minutes = document.getElementById('clock_display_minutes');
		clock_display_hours.replaceChild(document.createTextNode(hours),clock_display_hours.firstChild);
		clock_display_minutes.replaceChild(document.createTextNode(minutes),clock_display_minutes.firstChild);
	}
}