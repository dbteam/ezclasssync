<div class="box-header">
	<h1>Class Sync Dashboard</h1>

	Those class entries are found in current installation:
</div>
<form action="" method="post" id="form-sync" name="JsonList">
	<div class="box-content">

		<table class="list">
			<tr>
				<th class="tight">
					<img src={'toggle-button-16x16.gif'|ezimage} width="16" height="16" alt="{'Invert selection.'|i18n( 'design/admin/class/classlist' )}" title="{'Invert selection.'|i18n( 'design/admin/class/classlist' )}" onclick="ezjs_toggleCheckboxes( document.JsonList, 'ExportIDArray[]' ); return false;" />
				</th>
				<th>ID</th>
				<th>Filename</th>
				<th>Class identifier</th>
				<th colspan="3" width="25%">Options</th>
			</tr>
			{foreach $fileList as $i => $file}
				<tr>
					<td>
						<input type="checkbox" name="ExportIDArray[]" value="{$file.filehash}" title="Select class for export."/>
					</td>
					<td>{$i|inc(1)}.</td>
					<td>{$file.filename}</td>
					<td>Identifier: <strong>{$file.identifier}</strong>, Attributes:
						<strong>{$file.attribute_count}</strong>
					</td>
					<td><a href={concat("classsync/check/", $file.filehash)|ezurl}>Check</a></td>
					<td><a href={concat("classsync/sync/", $file.filehash)|ezurl}>Sync / Install</a></td>
				</tr>
			{/foreach}
		</table>
	</div>

	<div class="block">
		<input class="button" type="submit" name="check" value="Check selected" onclick='$("#form-sync").attr("action", {'classsync/check'|ezurl});'>
		<input class="button" type="submit" name="sync" value="Sync / Install selected" onclick='$("#form-sync").attr("action", {'classsync/sync'|ezurl});'>
	</div>

</form>
