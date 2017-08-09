<?php
return '
<div class="row-fluid">
	<div class="span12">
		<textarea id="input_content" class="span12" rows="20">
<h2>Hello World!</h2>

<p>
	It seems you just have installed the (rather empty) <cite>Hydrogen</cite> module <cite>App:Site</cite>.<br/>
	To go on, consider to install an application module or start creating HTML files in locale HTML folders.<br/>
</p>
<hr/>
		</textarea>
	</div>
</div>
<script>
jQuery(document).ready(function(){
	ModuleAce.applyTo("#input_content");
});
</script>
';
?>
