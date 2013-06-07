<h1>Select classes to export</h1>

<form name="ClassList" action={"classsync/export"|ezurl} method="post">
	<table class="list">
		<tr>
			<th class="tight">
				<img src={'toggle-button-16x16.gif'|ezimage} width="16" height="16" alt="{'Invert selection.'|i18n( 'design/admin/class/classlist' )}" title="{'Invert selection.'|i18n( 'design/admin/class/classlist' )}" onclick="ezjs_toggleCheckboxes( document.ClassList, 'ExportIDArray[]' ); return false;" />
			</th>
			<th class="tight">ID</th>
			<th>Name</th>
			<th>Identifier</th>
			<th>Modifier</th>
			<th>Modified</th>
			<th class="tight">&nbsp;</th>
			<th class="tight">&nbsp;</th>
		</tr>
		{foreach $classes as $class}
			<tr>
				<td>
					<input type="checkbox" name="ExportIDArray[]" value="{$class.id}" title="Select class for export."/>
				</td>
				<td class="number" align="right">{$class.id}</td>
				<td>{$class.identifier|class_icon( small, $class.name|wash )}&nbsp;<a href={concat( "/class/view/", $class.id )|ezurl}>{$class.name|wash}</a>
					{if gt($class.object_count,0)}({$class.object_count}){/if}
				</td>
				<td>{$class.identifier|wash}</td>
				<td>{content_view_gui view=text_linked content_object=$class.modifier.contentobject}</td>
				<td>{$class.modified|l10n( shortdatetime )}</td>
				<td>
					<a href={concat( 'classsync/export/', $class.id )|ezurl}><img class="button" src={"button-move_down.gif"|ezimage} width="16" height="16" alt="export" /></a>
				</td>
			</tr>
		{/foreach}
	</table>

	<div class="block">
		<input class="button" type="submit" name="zip" value="Export selected & download (zip)">
		<input class="button" type="submit" name="var" value="Export selected to /var/sync/">
		<input class="button" type="submit" name="extension" value="Export selected to /extension/ezclassync/sync/">
	</div>
</form>
