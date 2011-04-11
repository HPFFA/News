{if $listHeader|isset}{include file=$listHeader}{/if}

{if $contentArray|isset && $contentDisplay|isset}
<div id="list">
    {foreach from=$contentArray item=item}
        {include file=$contentDisplay item=$item}
    {/foreach}
</div>
{/if}

{if $listFooter|isset}[include file=$listFooter|isset}{/if}