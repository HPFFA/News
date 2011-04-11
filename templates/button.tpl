<li>
	<a
		href={$reference}
		id={$id}
		title="{lang}{@$title}{/lang}">
		{if $icon|isset}
			<img
				src="{icon}{$icon}{/icon}"
				alt="{if $iconAlt|isset}{@$iconAlt}{/if}" />
		{/if}
		<span>
			{lang}{@$title}{/lang}
		</span>
		{if $additional|isset}
			{$addditional}
		{/if}
	</a>
</li>