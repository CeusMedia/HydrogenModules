<?php
return '
<div class="content-panel">
	<h3>CAPTCHA-Test</h3>
	<div class="content-panel-inner">
		<form action="./captcha/test" method="post">
			<input type="text" name="captcha" data-captcha-instant="yes" required="required"/>
			[captcha]
			<button type="submit" name="save">save</button>
		</form>
	</div>
</div>';
