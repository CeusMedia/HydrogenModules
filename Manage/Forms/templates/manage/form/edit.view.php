<?php

$iconList	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
$iconPrev	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconNext	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-right' ) );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span10 offset0" id="shadow-form" style="background-color: rgba(191, 191, 191, 0.1); border: 1px solid rgba(191, 191, 191, 0.25); padding: 1em 2em;"></div>
		</div>
		<br/>
		<div class="buttonbar">
			'.$navButtons['list'].'
			'.$navButtons['prevFacts'].'
			'.$navButtons['nextBlocks'].'
		</div>
	</div>
</div>';
