<?php

/** @var array<string> $rights */
/** @var array<string,array<string,string>> $words */

if( !in_array( 'scan', $rights ) )
	return '';
$w	= (object) $words['scan'];

return '
<div class="content-panel">
	<h4>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		<p>
			'.$w->text.'
		</p>
		<div class="buttonbar">
			<a href="./manage/download/scan" class="btn btn-mini"><i class="icon-repeat"></i> '.$w->buttonScan.'</a>
		</div>
	</div>
</div>';
