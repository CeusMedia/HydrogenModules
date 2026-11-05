<?php

/** @var array<array<string,string>> $words */

$w	= (object) $words['test'];

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./captcha/test" method="post">
			<input type="text" name="captcha" data-captcha-instant="yes" required="required"/>
			[captcha]
			<button type="submit" name="save">'.$w->buttonSave.'</button>
		</form>
	</div>
</div>';
