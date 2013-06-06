<h2>Comparing JSON & Class:</h2>

{foreach $compareResults as $class}
	<h1>{$class.class}</h1>
	{if gt($class.differences, 0)}
		<p>There's {$class.differences} difference(s) in total.</p>
		{if ne($class.attrToAdd,'')}Attributes to add: {$class.attrToAdd}<br/>{/if}
		{if ne($class.attrToDrop,'')}Attributes to drop: {$class.attrToDrop}<br/>{/if}
		{if ne($class.attrToUp,'')}Attributes to update: {$class.attrToUp}<br/>{/if}
		<div class="block">
			<form method="get" action={concat("classsync/sync/", $class.formFileData)|ezurl}>
				<input class="defaultbutton" type="submit" value="Update & Sync">
			</form>
		</div>
		<div class="block" style="border: 5px solid red; padding: 0 5px; background: tomato; text-align: center;">
			<p><strong>Don't forget to make database backup before applying changes!</strong></p>
		</div>
	{else}
		<p>Looks that there's no differences.</p>
	{/if}
	{if not($class.compareResultClass|count)}
		<p>This class will be installed!</p>
		<div class="block">
			<form method="get" action={concat("classsync/sync/", $class.formFileData)|ezurl}>
				<input class="defaultbutton" type="submit" value="Install">
			</form>
		</div>
	{/if}

	{if lt($compareResults|count,2)}
		<table class="list">
			{if $class.compareResultClass|count}
				<tr>
					<th>Param</th>
					<th width="40%">Config Value</th>
					<th width="40%">Class Value</th>
				</tr>
				{foreach $class.compareResultClass as $data}
					<tr style="background-color: {if $data.isSame}yellowgreen{else}tomato{/if}">
						<td>{$data.param}</td>
						<td{if not($data.isDefault)} style="background-color: darkcyan;"{/if}>
							<code>{$data.value|wash()}</code>
						</td>
						<td><code>{$data.classValue|wash()}</code></td>
					</tr>
				{/foreach}
			{/if}
			{foreach $class.compareResultAttribute as $attr}
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
	{else}
		Details:
		<a href={concat("classsync/check/", $class.formFileData)|ezurl}>view</a>
	{/if}
{/foreach}

<p>
	<hr/>
	<a href={"classsync/dashboard"|ezurl()}>Back</a>
</p>

