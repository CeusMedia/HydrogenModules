<?php
return '
<div class="navbar navbar-inverse">
	<div class="navbar-inner">
		<div class="container">
			<a href="'.$baseUrl.'" class="brand">
				<!--<i class="icon-fire icon-white"></i> -->Office
			</a>
		</div>
	</div>
</div>
<div class="container">
	<br/>
	'.$heading.'
	<div class="text-greeting text-info">'.$greeting.'</div>
	<h4>'.$w->facts.': '.$link.'</h4>
	<div>'.$list.'</div>
<!--
	<div class="text-salute">'.$salute.'</div>
	<div class="text-signature">'.$w->textSignature.'</div>
-->
</div>';
?>
