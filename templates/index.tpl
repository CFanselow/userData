{**
 * plugins/importexport/emailAddress/templates/index.tpl
 *
 * Copyright (c) 2016-2019
 * Copyright (c) 2020 Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.importexport.emailAddress.displayName"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#exportTab').pkpHandler('$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="exportTab" class="pkp_controllers_tab">
	<ul>
		<li><a href="#export-tab">{translate key="plugins.importexport.emailAddress.export"}</a></li>
	</ul>
	<div id="export-tab">
		<script type="text/javascript">
			$(function() {ldelim}
				// Attach the form handler.
				$('#exportForm').pkpHandler('$.pkp.controllers.form.FormHandler');
			{rdelim});
		</script>
		<form id="exportForm" class="pkp_form" action="{plugin_url path="export"}" method="post">
			{csrf}				
			<h3>{translate key="plugins.importexport.emailAddress.title"}</h3>
			<br/>
			{fbvFormArea id="dateRegistered"}
				{fbvFormSection list=true}
					{fbvElement type="checkbox" id="useDateRegistered" name="useDateRegistered" label="plugins.importexport.emailAddress.dateCheckbox"}				
				{/fbvFormSection}
				{fbvFormSection}
					{fbvElement type="text" id="dateRegistered" translate=false label="plugins.importexport.emailAddress.date" inline=true size=$fbvStyles.size.MEDIUM class="datepicker"}
				{/fbvFormSection}
			{/fbvFormArea}
			<p>{translate key="plugins.importexport.emailAddress.intro"}</p>			
			<div>
				<table>
					<tr style="text-align:left">
						<th>{translate key="plugins.importexport.emailAddress.or"}</th>
						<th>{translate key="plugins.importexport.emailAddress.and"}</th>
						<th>{translate key="plugins.importexport.emailAddress.not"}</th>
						<th>{translate key="plugins.importexport.emailAddress.userGroup"}</th>
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
			{fbvFormButtons submitText="plugins.importexport.emailAddress.export" hideCancel="true"}
		</form>
	</div>
</div>

{include file="common/footer.tpl"}
