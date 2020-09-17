{**
 *
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * 
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#emailAddressForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="emailAddressForm" method="post" action="{url op="saveEmailAddress"}">
	<h3>{translate key="plugins.generic.emailAddress.title"}</h3>
	<p>{translate key="plugins.generic.emailAddress.intro"}</p>
	<div>
		<table>
			<tr style="text-align:left">
				<th>{translate key="plugins.generic.emailAddress.or"}</th>
				<th>{translate key="plugins.generic.emailAddress.and"}</th>
				<th>{translate key="plugins.generic.emailAddress.not"}</th>
				<th>{translate key="plugins.generic.emailAddress.userGroup"}</th>
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

	{fbvFormButtons id="emailAddressFormSubmit" submitText="plugins.generic.emailAddress.download" hideCancel=true}
</form>
