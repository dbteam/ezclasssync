<h1>Class Sync Dashboard</h1>

Those class entries are found in current installation:

<table class="list">
	<tr>
		<th>ID</th>
		<th>Filename</th>
		<th>Class identifier</th>
		<th colspan="3" width="25%">Options</th>
	</tr>
	{foreach $fileList as $i => $file}
		<tr>
			<td>{$i|inc(1)}.</td>
			<td>{$file.filename}</td>
			<td>Identifier: <strong>{$file.identifier}</strong>, Attributes: <strong>{$file.attribute_count}</strong></td>
			<td><a href={concat("classsync/check/", $file.filehash)|ezurl}>Check</a></td>
			<td><a href={concat("classsync/sync/", $file.filehash)|ezurl}>Sync / Install</a></td>
		</tr>
	{/foreach}
</table>
