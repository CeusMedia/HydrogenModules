<?php
return '
<style>
.content-panel-inner dl.dl-horizontal dt {
	width: 110px;
	}
.content-panel-inner dl.dl-horizontal dd {
	margin-left: 130px;
	}
</style>

<div class="navbar navbar-inverse">
	<div class="navbar-inner">
		<div class="container">
			<a href="'.$baseUrl.'" class="brand">
				<i class="icon-fire icon-white"></i> Office
			</a>
		</div>
	</div>
</div>
<div class="container">
	<br/>
<!--	'.$heading.'-->
<!--	<div class="text-greeting text-info">'.$greeting.'</div>
	<br/>-->
	<div class="content-panel">
		<h4><!--'.$words->facts.': -->'.$link.'</h4>
		<div class="content-panel-inner">'.$list.'</div>
		<div class="">'.$content.'</div>
<!--
		<div class="text-salute">'.$salute.'</div>
		<div class="text-signature">'.$words->textSignature.'</div>
-->
	</div>
</div>';
?>
