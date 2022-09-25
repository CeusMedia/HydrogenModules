<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['index-unrelated'];

$list	= HtmlTag::create( 'div', '<em class="muted">'.$w->empty.'</em>', array( 'class' => 'alert alert-info' ) );

if( $unrelatedTimers ){
	$list	= [];
	foreach( $unrelatedTimers as $timer ){
		$label	= $timer->title;
		$link	= HtmlTag::create( 'a', $label, array( 'href' => './work/time/edit/'.$timer->workTimerId ) );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	$list	= HtmlTag::create( 'ul', $list, array( 'class' => 'not-unstyled' ) );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
