{**
 * plugins/generic/emailAddress/templates/toolsNavLink.tpl
 *
 * Copyright (c) 2020 Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 *}
<li>
	<a name="pixelTags" href="
	{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.emailAddress.emailAddressHandler" op="fetchEmailAddress" tab="emailAddressTab"}">
	{translate key="plugins.generic.emailAddress.linkName"}
	</a>
</li>