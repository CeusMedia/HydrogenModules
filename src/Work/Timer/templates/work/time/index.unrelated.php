<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var array $unrelatedTimers */

$w		= (object) $words['index-unrelated'];

$list	= HtmlTag::create( 'div', '<em class="muted">'.$w->empty.'</em>', ['class' => 'alert alert-info'] );

if( $unrelatedTimers ){
	$list	= [];
	foreach( $unrelatedTimers as $timer ){
		$label	= $timer->title;
		$link	= HtmlTag::create( 'a', $label, ['href' => './work/time/edit/'.$timer->workTimerId] );
		$list[]	= HtmlTag::create( 'li', $link );
	}
	$list	= HtmlTag::create( 'ul', $list, ['class' => 'not-unstyled'] );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
