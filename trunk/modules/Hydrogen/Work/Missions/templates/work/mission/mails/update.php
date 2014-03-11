<?php
return '
<div class="container">
	<div class="navbar navbar-inverse">
		<div class="navbar-inner">
			<a href="'.$baseUrl.'" class="brand">
				<!--<i class="icon-fire icon-white"></i> -->Office
			</a>
		</div>
	</div>
	<br/>
	'.$heading.'
	<div class="text-greeting text-info">'.$greeting.'</div>
	<h4>'.$type.': '.$link.'</h4>
	<div class="tasks">'.$list.'</div>
<!--
	<div class="text-salute">'.$salute.'</div>
	<div class="text-signature">'.$w->textSignature.'</div>
-->
</div>';
?>
