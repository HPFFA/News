{include file="documentHeader"}
<head>
	<title>{lang}de.static.site.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>

	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">

	<div class="mainHeadline">
		<img src="{icon}staticSiteL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}de.static.site.headline{/lang}</h2>
			<h3>{lang}de.static.site.description{/lang}</h3>
		</div>
	</div>

	{if $userMessages|isset}{@$userMessages}{/if}

	{if $additionalTopContents|isset}{@$additionalTopContents}{/if}

	<div class="border">

        {if $listHeader|isset}{include file=$listHeader}{/if}

		{if $contentArray|isset && $contentDisplay|isset}
			<div id="list">
	    		{foreach from=$contentArray item=item}
	        		{include file=$contentDisplay item=$item}
	    		{/foreach}
			</div>
		{/if}

		{if $listFooter|isset}[include file=$listFooter|isset}{/if}

	</div>

	<div class="largeButtons">
		<ul>
			{include file="button" reference="index.php?form=NewsAdd" id="replyButton" title="de.hpffa.news.new" icon="messageAddM.png"}
		</ul>
	</div>

</div>

{include file='footer' sandbox=false}

</body>
</html>