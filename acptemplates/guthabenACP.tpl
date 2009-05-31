<fieldset id="guthaben">
	<legend>{lang}wbb.acp.board.guthaben{/lang}</legend>

	<div class="formElement" id="countGuthabenDiv">
		<div class="formField">
			<label id="countGuthaben"><input type="checkbox" name="countGuthaben" value="1" {if $countGuthaben}checked="checked" {/if}/> {lang}wbb.acp.board.countGuthaben{/lang}</label>
		</div>
		<div class="formFieldDesc hidden" id="countGuthabenHelpMessage">
			{lang}wbb.acp.board.countGuthaben.description{/lang}
		</div>
	</div>
	<script type="text/javascript">//<![CDATA[
		inlineHelp.register('countGuthaben');
	//]]></script>

	<div class="formElement" id="threadAddGuthabenDiv">
		<div class="formFieldLabel">
			<label for="image">{lang}wbb.acp.board.threadAddGuthaben{/lang}</label>
		</div>
		<div class="formField">
			<input type="text" class="inputText" id="threadAddGuthaben" name="threadAddGuthaben" value="{$threadAddGuthaben}" />
		</div>
		<div class="formFieldDesc hidden" id="threadAddGuthabenHelpMessage">
			{lang}wbb.acp.board.threadAddGuthaben.description{/lang}
		</div>
	</div>
	<script type="text/javascript">//<![CDATA[
	inlineHelp.register('threadAddGuthaben');
	//]]></script>

	<div class="formElement" id="postAddGuthabenDiv">
		<div class="formFieldLabel">
			<label for="image">{lang}wbb.acp.board.postAddGuthaben{/lang}</label>
		</div>
		<div class="formField">
			<input type="text" class="inputText" id="postAddGuthaben" name="postAddGuthaben" value="{$postAddGuthaben}" />
		</div>
		<div class="formFieldDesc hidden" id="postAddGuthabenHelpMessage">
			{lang}wbb.acp.board.postAddGuthaben.description{/lang}
		</div>
	</div>
	<script type="text/javascript">//<![CDATA[
	inlineHelp.register('postAddGuthaben');
	//]]></script>
</fieldset>