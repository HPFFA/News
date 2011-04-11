
{if $item|isset}

    <div class="border container-1">
    	<h1>Title:&nbsp {@$item->title}{if $item->newsID}  (ID:&nbsp {$item->newsID}){/if}</h1>
		{if $item->summary}
        	Summary:<br>{@$item->summary}<br><br>
		{/if}
        Text:<br>{@$item->text}<br><br>
        (Time: {$item->time})
        <i>
        	Author:&nbsp
            {@$item->authorname}
            {if $item->authorID}
            	({$item->authorID})
			{/if}
		</i>
    </div>
	{if !$preview|isset}
			<div class="smallButtons">
				<ul>
					{include
						file="button"
						reference="index.php?form=NewsEdit&newsID="|concat:$item->newsID
						id="replyButton"
						title="de.hpffa.news.edit"
						icon="messageAddM.png"}
				</ul>
			</div>
		{/if}
{/if}