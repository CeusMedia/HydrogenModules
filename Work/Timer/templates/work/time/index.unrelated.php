<?php

$w		= (object) $words['index-unrelated'];

$list	= UI_HTML_Tag::create( 'div', '<em class="muted">'.$w->empty.'</em>', array( 'class' => 'alert alert-info' ) );

if( $unrelatedTimers ){
	$list	= array();
	foreach( $unrelatedTimers as $timer ){
		$label	= $timer->title;
		$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => './work/time/edit/'.$timer->workTimerId ) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'not-unstyled' ) );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
