{**
 * plugins/importexport/userData/templates/index.tpl
 *
 * Copyright (c) 2016-2019
 * Copyright (c) 2020 Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.userData.displayName"}
{include file="common/header.tpl"}
{/strip}

<style>
#ui-datepicker-div {
display:none;
}
</style>

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#exportTab').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="exportTab" class="pkp_controllers_tab">
	<ul>
		<li><a href="#export-all-data-tab">{translate key="plugins.importexport.userData.exportAllData"}</a></li>
		<li><a href="#export-selection-tab">{translate key="plugins.importexport.userData.exportSelection"}</a></li>
	</ul>
	<div id="export-all-data-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#exportAllDataForm').pkpHandler('$.pkp.controllers.form.FormHandler');
			{rdelim});
		</script>
		<form id="exportAllDataForm" class="pkp_form" action="{plugin_url path="exportAllData"}" method="post">
			{csrf}				
			<h3>{translate key="plugins.importexport.userData.title.exportAllData"}</h3>
			<p>{translate key="plugins.importexport.userData.description.exportAllData"}</p>
			
			{fbvFormArea class="dateRegistered"}
				
			{/fbvFormArea}			
			{fbvFormButtons submitText="plugins.importexport.userData.export" hideCancel="true"}
		</form>
	</div>		
	<div id="export-selection-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#exportSelectionForm').pkpHandler('$.pkp.controllers.form.FormHandler');
			{rdelim});
		</script>
		<form id="exportSelectionForm" class="pkp_form" action="{plugin_url path="exportSelection"}" method="post">
			{csrf}				
			<h3>{translate key="plugins.importexport.userData.title.exportSelection"}</h3>
			<p>{translate key="plugins.importexport.userData.description.exportSelection"}</p>			
			<br/>
			{fbvFormArea class="dateRegistered"}
				{fbvFormSection list=true}
					{fbvElement type="checkbox" id="useDateRegistered" name="useDateRegistered" label="plugins.importexport.userData.dateCheckbox"}				
				{/fbvFormSection}
				{fbvFormSection}
					{fbvElement type="text" id="dateRegistered" translate=false label="plugins.importexport.userData.date" inline=true size=$fbvStyles.size.MEDIUM class="datepicker"}
				{/fbvFormSection}
			{/fbvFormArea}
			<p>{translate key="plugins.importexport.userData.intro"}</p>			
			<div>
				<table>
					<tr style="text-align:left">
						<th>{translate key="plugins.importexport.userData.or"}</th>
						<th>{translate key="plugins.importexport.userData.and"}</th>
						<th>{translate key="plugins.importexport.userData.not"}</th>
						<th>{translate key="plugins.importexport.userData.userGroup"}</th>
					</tr>

					{assign var="count" value=0}
					{foreach from=$userGroups key=userGroupId item=groupName}
						<tr {if $count mod 2} class="even"{else}class="odd"{/if}>
							<td>
								<input id="OR{$userGroupId}" name="OR{$userGroupId}" type="checkbox"></input>
							</td>
							<td>
								<input id="AND{$userGroupId}" name="AND{$userGroupId}" type="checkbox"></input>
							</td>
							<td>
								<input id="NOT{$userGroupId}" name="NOT{$userGroupId}" type="checkbox"></input>
							</td>
							<td>
								<span>{$groupName}</span>
							</td>
						</tr>
					{assign var=count value=$count+1}
					{/foreach}
				</table>
				</br>
			</div>	
			{fbvFormButtons submitText="plugins.importexport.userData.export" hideCancel="true"}
		</form>
	</div>
</div>

{include file="common/footer.tpl"}
