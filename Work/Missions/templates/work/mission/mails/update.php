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
<div class="container">
	<div class="navbar navbar-inverse">
		<div class="navbar-inner">
			<a href="'.$baseUrl.'" class="brand">
				<!--<i class="icon-fire icon-white"></i> -->Office
			</a>
		</div>
	</div>
	<br/>
<!--	'.$heading.'-->
<!--	<div class="text-greeting text-info">'.$greeting.'</div>-->
	<div class="content-panel">
		<h4>'.$type.': '.$link.'</h4>
		<div class="content-panel-inner">
			<div class="tasks">'.$list.'</div>
			<div class="content">'.$content.'</div>
<!--			<div class="content">'.nl2br( $new->content ).'</div>
<!--
			<div class="text-salute">'.$salute.'</div>
			<div class="text-signature">'.$words->textSignature.'</div>
-->
		</div>
	</div>
</div>';
?>
