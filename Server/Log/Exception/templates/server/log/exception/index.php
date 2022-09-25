<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['index'];

$iconView	= HtmlTag::create( 'i', '', array( 'class' => 'icon-eye-open not-icon-white' ) );
$iconRemove	= HtmlTag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconView	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
	$iconRemove	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
}

$list	= '<div class="muted"><em><small>No exceptions logged.</small></em></div>';
if( $exceptions ){
	$list	= [];
	foreach( $exceptions as $item ){
		$link	= HtmlTag::create( 'a', $item->message, array(
			'href'	=> './server/log/exception/view/'.$item->exceptionId,
			'class'	=> 'autocut',
		) );
		$date	= date( 'Y.m.d', $item->createdAt );
		$time	= date( 'H:i:s', $item->createdAt );
		$buttons	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', $iconView, array(
				'class'	=> 'btn btn-mini not-btn-info',
				'href'	=> './server/log/exception/view/'.$item->exceptionId,
				'title'	=> $w->buttonView,
			) ),
			HtmlTag::create( 'a', $iconRemove, array(
				'class'	=> 'btn btn-mini btn-danger',
				'href'	=> './server/log/exception/remove/'.$item->exceptionId,
				'title'	=> $w->buttonRemove,
			) ),
		), array( 'class' => 'btn-group' ) );

		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link, array( 'class' => 'autocut' ) ),
			HtmlTag::create( 'td', $item->type ),
			HtmlTag::create( 'td', $date.'&nbsp;<small class="muted">'.$time.'</small>' ),
			HtmlTag::create( 'td', $buttons ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( '', '180px', '150px', '100px' );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$tbody, array( 'class' => 'table table-striped table-condensed', 'style' => 'table-layout: fixed' ) );
}

$pagination	= new \CeusMedia\Bootstrap\PageControl( './server/log/exception', $page, ceil( $total / $limit ) );
$pagination	= $pagination->render();

$panelList	= '
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				'.$list.'
				'.$pagination.'
			</div>
		</div>
';
return $panelList;

return '
<div class="row-fluid">
	<div class="span3">
		<div class="content-panel">
			<h3>Filter</h3>
			<div class="content-panel-inner">
			</div>
		</div>
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>
';
