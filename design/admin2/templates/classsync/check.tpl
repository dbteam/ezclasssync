<h2>Comparing JSON & Class:</h2>
<h1>{$class}</h1>


{if gt($differences, 0)}
	<p>There's {$differences} difference(s) in total.</p>
	{if ne($attrToAdd,'')}Attributes to add: {$attrToAdd}<br/>{/if}
	{if ne($attrToDrop,'')}Attributes to drop: {$attrToDrop}<br/>{/if}
	{if ne($attrToUp,'')}Attributes to update: {$attrToUp}<br/>{/if}
	<div class="block">
		<input class="defaultbutton" value="Update & Sync">
	</div>
	<div class="block" style="border: 5px solid red; padding: 0 5px; background: tomato; text-align: center;">
		<p><strong>Don't forget to make database backup before applying changes!</strong></p>
	</div>
{else}
	<p>Looks that there's no differences. Yay!</p>
{/if}
{if not($compareResultClass|count)}
	<p>This class will be installed!</p>
	<div class="block">
		<input class="defaultbutton" value="Install">
	</div>
{/if}

<table class="list">
	{if $compareResultClass|count}
		<tr>
			<th>Param</th>
			<th width="40%">Config Value</th>
			<th width="40%">Class Value</th>
		</tr>
		{foreach $compareResultClass as $data}
			<tr style="background-color: {if $data.isSame}yellowgreen{else}tomato{/if}">
				<td>{$data.param}</td>
				<td{if not($data.isDefault)} style="background-color: darkcyan;"{/if}><code>{$data.value|wash()}</code>
				</td>
				<td><code>{$data.classValue|wash()}</code></td>
			</tr>
		{/foreach}
	{/if}
	{foreach $compareResultAttribute as $attr}
		<tr>
			<th colspan="3">{$attr.identifier}</th>
		</tr>
		{if $attr.values|count}
			<tr>
				<th>Param</th>
				<th width="40%">Config Value</th>
				<th width="40%">Class Value</th>
			</tr>
			{foreach $attr.values as $data}
				{def $bgColor = 'yellowgreen'}
				{if not($data.isSame)}
					{set $bgColor = 'tomato'}
				{/if}
				{if ne($attr.side,'b')}
					{set $bgColor = 'white'}
				{/if}
				<tr style="background-color: {$bgColor};">
					<td>{$data.param}</td>
					<td{if eq($attr.side,'r')} style="background-color: orange;"{/if}>
						{if eq($attr.side,'l')}&times;{else}<code>{$data.value|wash()}</code>{/if}
					</td>
					<td{if eq($attr.side,'l')} style="background-color: orange;"{/if}>
						{if eq($attr.side,'r')}&times;{else}<code>{$data.classValue|wash()}</code>{/if}
					</td>
				</tr>
			{/foreach}
		{/if}
	{/foreach}
	<tr>
		<th colspan="3">&nbsp;</th>
	</tr>
</table>

