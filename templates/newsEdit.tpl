{include file="documentHeader"}
<head>
	<title>{lang}de.hpffa.news.pageTitle{/lang} - {lang}{PAGE_TITLE}{/lang}</title>

	{include file='headInclude' sandbox=false}

	{include file='imageViewer'}

	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabbedPane.class.js"></script>
	<script type="text/javascript">
		//<![CDATA[
		var INLINE_IMAGE_MAX_WIDTH = {@INLINE_IMAGE_MAX_WIDTH};
		//]]>
	</script>
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/ImageResizer.class.js"></script>
	{if $canUseBBCodes}{include file="wysiwyg"}{/if}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">

	<div class="mainHeadline">
		<img src="{icon}storyNewL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}de.hpffa.news.edit{/lang}</h2>
		</div>
	</div>

	{if $debug}
		<p class="debug">
			{$debug}
		</p>
	{/if}
	{if $errorField}
                {if $errorField == 'news'}
                    <p class="error">
                            {if $errorType == 'duplicate'}{lang}de.hpffa.news.error.duplicate{/lang}{/if}
                    </p>
                {else}
                    <p class="error">{lang}wcf.global.form.error{/lang}</p>
                {/if}
	{/if}

        {if $preview|isset}
		<div class="border messagePreview">
			<div class="containerHead">
				<h3>{lang}wcf.message.preview{/lang}</h3>
			</div>
                        {include file='newsItem' item=$preview preview=''}
		</div>
	{/if}

	<form enctype="multipart/form-data" method="post" action="index.php?form=newsEdit&amp;newsID={$newsID}">
		<div class="border content">
                    <div class="container-1">
                        <fieldset>
                            <legend>de.hpffa.news.editContent</legend>


                            <div class="formElement{if $errorField == 'title'} formError{/if}">
                                    <div class="formFieldLabel">
                                            <label for="title">{lang}de.hpffa.news.title{/lang}</label>
                                    </div>
                                    <div class="formField">
                                            <input type="text" class="inputText" name="title" id="title" value="{@$title}" tabindex="{counter name='tabindex'}" />
                                            {if $errorField == 'title'}
                                                    <p class="innerError">
                                                            {if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
                                                    </p>
                                            {/if}
                                    </div>
                            </div>

                            {if MODULE_TAGGING && STORY_ENABLE_TAGS}{include file='tagAddBit'}{/if}

                            <div class="formElement">
                                <div class="formFieldLabel">
                                    <label for="summary">{lang}de.hpffa.news.summary{/lang}</label>
                                </div>
                                <div class="formField">
                                    <textarea name="summary" id="summary" rows="5" cols="40" tabindex="{counter name='tabindex'}">{if $summary|isset}{$summary}{/if}</textarea>
                                    {if $errorField == 'summary'}
                                            <p class="innerError">
                                                    {if $errorType == 'tooLong'}{lang}wcf.message.error.tooLong{/lang}{/if}
                                                    {if $errorType == 'censoredWordsFound'}{lang}wcf.message.error.censoredWordsFound{/lang}{/if}
                                                    {if $errorType == 'tooShort'}{lang}sls.storyAdd.summary.error.tooShort{/lang}{/if}
                                                </p>
                                    {/if}
                                </div>
                            </div>
                            <div class="formElement">
                                <div class="formFieldLabel">
                                    <label for="text">{lang}de.hpffa.news.text{/lang}</label>
                                </div>
                                <div class="formField">
                                    <textarea name="text" id="text" rows="15" cols="40" tabindex="{counter name='tabindex'}">{$text}</textarea>
                                    {if $errorField == 'text'}
                                            <p class="innerError">
                                                    {if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
                                                    {if $errorType == 'tooLong'}{lang}wcf.message.error.tooLong{/lang}{/if}
                                                    {if $errorType == 'censoredWordsFound'}{lang}wcf.message.error.censoredWordsFound{/lang}{/if}
                                                    {if $errorType == 'tooShort'}{lang}sls.storyAdd.summary.error.tooShort{/lang}{/if}
                                            </p>
                                    {/if}
                                </div>
                            </div>
                            <div class="formElement{if $errorField == 'editReason'} formError{/if}">
								<div class="formFieldLabel">
									<label for="editReason">{lang}de.hpffa.news.editReason{/lang}</label>
								</div>
								<div class="formField">
									<input type="text" class="inputText" name="editReason" id="editReason" value="{if $editReason|isset}{$editReason}{/if}" tabindex="{counter name='tabindex'}" />
									{if $errorField == 'editReason'}
										<p class="innerError">
											{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
											{if $errorType == 'tooShort'}{lang}de.hpffa.news.error.tooShort{/lang}{/if}
											{if $errorType == 'censoredWordsFound'}{lang}wcf.message.error.censoredWordsFound{/lang}{/if}
											{if $errorType == 'tooLong'}{lang}wcf.message.error.tooLong{/lang}{/if}
										</p>
									{/if}
								</div>
							</div>
                            {if !$this->user->userID}
                                <div class="formElement{if $errorField == 'authorname'} formError{/if}">
                                    <div class="formFieldLabel">
                                        <label for="authorname">{lang}wcf.user.username{/lang}</label>
                                    </div>
                                    <div class="formField">
                                        <input type="text" class="inputText" name="authorname" id="authorname" value="{$authorname}" tabindex="{counter name='tabindex'}" />
                                        {if $errorField == 'authorname'}
                                            <p class="innerError">
                                                {if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
                                                {if $errorType == 'notValid'}{lang}wcf.user.error.username.notValid{/lang}{/if}
                                                {if $errorType == 'notAvailable'}{lang}wcf.user.error.username.notUnique{/lang}{/if}
                                            </p>
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                        </fieldset>
			{include file='messageFormTabs'}
                	{include file='captcha'}
                        <div class="formSubmit">
                                <input type="submit" name="send" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" tabindex="{counter name='tabindex'}" />
                                <input type="submit" name="preview" accesskey="p" value="{lang}wcf.global.button.preview{/lang}" tabindex="{counter name='tabindex'}" />
                                <input type="reset" name="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" tabindex="{counter name='tabindex'}" />
                                {@SID_INPUT_TAG}
                        </div>
                    </div>
                </div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>
