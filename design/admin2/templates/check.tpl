<h2>Comparing JSON & Class:</h2>
<p>There's {$differences} difference(s) in total.</p>
{if gt($differences, 0)}
	{if ne($attrToAdd,'')}Attributes to add: {$attrToAdd}<br/>{/if}
	{if ne($attrToDrop,'')}Attributes to drop: {$attrToDrop}<br/>{/if}
	{if ne($attrToUp,'')}Attributes to update: {$attrToUp}<br/>{/if}
	<div class="block">
		<input class="defaultbutton" value="Update & Sync">
	</div>
	<div class="block" style="border: 5px solid red; padding: 0 5px; background: tomato; text-align: center;">
		<p><strong>Don't forget to make database backup before applying changes!</strong></p>
	</div>
{/if}

<table class="list">
	<tr>
		<th>Param</th>
		<th width="40%">Config Value</th>
		<th width="40%">Class Value</th>
	</tr>
	{foreach $compareResultClass as $data}
		<tr style="background-color: {if $data.isSame}yellowgreen{else}tomato{/if}">
			<td>{$data.param}</td>
			<td{if not($data.isDefault)} style="background-color: darkcyan;"{/if}><code>{$data.value|wash()}</code></td>
			<td><code>{$data.classValue|wash()}</code></td>
		</tr>
	{/foreach}
	{foreach $compareResultAttribute as $attr}
		<tr>
			<th colspan="3">{$attr.identifier}</th>
		</tr>
		<tr>
			<th>Param</th>
			<th width="40%">Config Value</th>
			<th width="40%">Class Value</th>
		</tr>
		{foreach $attr.values as $data}
			{def $bgColor = 'yellowgreen'}
			{if not($data.isSame)}
				{if eq($data.side, 'b')}
					{set $bgColor = 'tomato'}
				{elseif eq($data.side, 'l')}
					{set $bgColor = 'white'}
				{else}
					{set $bgColor = 'white'}
				{/if}
			{/if}
			<tr style="background-color: {$bgColor};">
				<td>{$data.param}</td>
				<td{if not($data.isDefault)} style="background-color: darkcyan;"{/if}>
					{if not($data.drop)}<code>{$data.value|wash()}</code>{else}&times;{/if}
				</td>
				<td{if eq($data.side,'r')} style="background-color: tomato;"{/if}><code>{$data.classValue|wash()}</code>
				</td>
			</tr>
		{/foreach}
	{/foreach}
	<tr>
		<th colspan="3">&nbsp;</th>
	</tr>
</table>

