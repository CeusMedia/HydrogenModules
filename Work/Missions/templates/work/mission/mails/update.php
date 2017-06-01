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
<div class="alert alert-info">'.sprintf( $words->message, $type, $link, $modifier ).'</div>
<div class="content-panel">
	<h3>'.$words->headingFacts.'</h3>
	<div class="content-panel-inner">
		<div class="facts">
			'.$list.'
		</div>
		<hr/>
		<div class="content">
			'.$content.'
		</div>
	</div>
</div>';
?>
