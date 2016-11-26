{*<!--
/*********************************************************************************
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
 ********************************************************************************/
-->*}
{strip}
{foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET']}
	{if ($DETAIL_VIEW_WIDGET->getLabel() eq 'Documents') }
		{assign var=DOCUMENT_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'ModComments')}
		{assign var=COMMENTS_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'LBL_UPDATES')}
		{assign var=UPDATES_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{/if}
{/foreach}

<div class="row-fluid">
	<div class="span7">
		{* Module Summary View*}
			<div class="summaryView row-fluid">
				<div class="recordDetails">
				{include file='SummaryViewContents.tpl'|@vtemplate_path}
				</div>
			</div>
		{* Module Summary View Ends Here*}

		{* Summary View Comments Widget*}
		{if $COMMENTS_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_comments" data-url="{$COMMENTS_WIDGET_MODEL->getUrl()}" data-name="{$COMMENTS_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="{$COMMENTS_WIDGET_MODEL->get('linkName')}" />
						<span class="span9 margin0px"><h4>{vtranslate($COMMENTS_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
						<span class="span3">
							<span class="pull-right">
								{if $COMMENTS_WIDGET_MODEL->get('action')}
									<button class="btn pull-right addButton createRecord" type="button" data-url="{$COMMENTS_WIDGET_MODEL->get('actionURL')}">
										<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
									</button>
								{/if}
							</span>
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Comments Widget Ends Here*}
	</div>

	<div class='span5' style="overflow: hidden">
		<div class="summaryWidgetContainer">
			<form action="index.php" method="post">
				<input type="hidden" name="module" value="{$MODULE_NAME}">
				<input type="hidden" name="record" value="{$RECORD->get('id')}">
				<input type="hidden" name="mode" value="edit">
				{if $SHOW_WATCH eq 'started'}<input type="hidden" name="action" value="Save">{/if}
				{if $SHOW_WATCH eq 'halted'}
				<input type="hidden" name="isDuplicate" value="true">
				<input type="hidden" name="view" value="Edit">
				{/if}
				<input type="hidden" name="stop_watch" value="{if $SHOW_WATCH eq 'halted'}0{else}1{/if}">
				<div class="widget_header row-fluid">
					<span class="span9 margin0px"><h4 class="textOverflowEllipsis">{vtranslate('Stopwatch',$MODULE_NAME)}</h4></span>
					<span class="span3">
						<span class="pull-right">
							<button class="btn pull-right addButton" type="submit">
								<strong>{if $SHOW_WATCH eq 'halted'}{vtranslate('LBL_WATCH_RESTART',$MODULE_NAME)}{else}{vtranslate('LBL_WATCH_STOP',$MODULE_NAME)}{/if}</strong>
							</button>
						</span>
					</span>
				</div>
				<div class="widget_contents" style="text-align: center; font-family: courier new, courier, monospace; font-size: 220%; font-weight: bold;">
				{if $SHOW_WATCH eq 'halted'}
					<img src="modules/Timecontrol/images/clock-red.gif" id="clock_image" style="vertical-align: middle; margin-right:16px;">
					<span id="clock_display" style="vertical-align: middle">{$WATCH_DISPLAY}</span>
				{/if}
				{if $SHOW_WATCH eq 'started'}
					<img src="modules/Timecontrol/images/clock-green.gif" id="clock_image" style="vertical-align: middle; margin-right:16px;">
					<span id="clock_display_hours" style="vertical-align: middle;">00</span><span id="clock_display_separator" style="vertical-align: middle;">:</span><span id="clock_display_minutes" style="vertical-align: middle;">00</span>
					<input id="clock_counter" type="hidden" value="{$WATCH_COUNTER}">
					<script type="text/javascript">
					var tc_clock;
					$(document).ready(function() {ldelim}
						clearInterval(tc_clock);
						updateClock(true);
						tc_clock = setInterval("updateClock()", 1000);
					{rdelim});
					</script>
				{/if}
				</div>
			</form>
		</div>

		{* Summary View Related Activities Widget*}
			<div id="relatedActivities">
				{$RELATED_ACTIVITIES}
			</div>
		{* Summary View Related Activities Widget Ends Here*}

		{* Summary View Documents Widget*}
		{if $DOCUMENT_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_documents" data-url="{$DOCUMENT_WIDGET_MODEL->getUrl()}" data-name="{$DOCUMENT_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="{$DOCUMENT_WIDGET_MODEL->get('linkName')}" />
						<span class="span9 margin0px"><h4 class="textOverflowEllipsis">{vtranslate($DOCUMENT_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
						<span class="span3">
							<span class="pull-right">
								{if $DOCUMENT_WIDGET_MODEL->get('action')}
									<button class="btn pull-right addButton createRecord" type="button" data-url="{$DOCUMENT_WIDGET_MODEL->get('actionURL')}">
										<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
									</button>
								{/if}
							</span>
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Documents Widget Ends Here*}

		{* Summary View Updates Widget*}
		{if $UPDATES_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_updates" data-url="{$UPDATES_WIDGET_MODEL->getUrl()}" data-name="{$UPDATES_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="{$UPDATES_WIDGET_MODEL->get('linkName')}" />
						<span class="span9 margin0px"><h4 class="textOverflowEllipsis">{vtranslate($UPDATES_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
						<span class="span3">
							<span class="pull-right">
								{if $UPDATES_WIDGET_MODEL->get('action')}
									<button class="btn pull-right addButton createRecord" type="button" data-url="{$UPDATES_WIDGET_MODEL->get('actionURL')}">
										<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
									</button>
								{/if}
							</span>
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Updates Widget Ends Here*}
	</div>
</div>
{/strip}