<?php

$w	= (object) $words['index'];

$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open not-icon-white' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
	$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
}

$list	= '<div class="muted"><em><small>No exceptions logged.</small></em></div>';
if( $exceptions ){
	$list	= array();
	foreach( $exceptions as $nr => $exception ){
		if( isset( $exception->exception ) ){
			$exception->message	= $exception->exception->getMessage();
			$exception->code	= $exception->exception->getCode();
			$exception->file	= $exception->exception->getFile();
			$exception->line	= $exception->exception->getLine();
		}
		$link	= UI_HTML_Tag::create( 'a', $exception->message, array(
			'href'	=> './server/log/exception/view/'.$exception->id,
			'class'	=> 'autocut',
		) );
		$date	= date( 'Y.m.d', $exception->timestamp );
		$time	= date( 'H:i:s', $exception->timestamp );
		$buttons	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', $iconView, array(
				'class'	=> 'btn btn-mini not-btn-info',
				'href'	=> './server/log/exception/view/'.$exception->id,
				'title'	=> $w->buttonView,
			) ),
			UI_HTML_Tag::create( 'a', $iconRemove, array(
				'class'	=> 'btn btn-mini btn-danger',
				'href'	=> './server/log/exception/remove/'.$exception->id,
				'title'	=> $w->buttonRemove,
			) ),
		), array( 'class' => 'btn-group' ) );

		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link, array( 'class' => 'autocut' ) ),
			UI_HTML_Tag::create( 'td', $date.'&nbsp;<small class="muted">'.$time.'</small>' ),
			UI_HTML_Tag::create( 'td', $buttons ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "", "150px", "100px" );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$tbody, array( 'class' => 'table table-striped table-condensed', 'style' => 'table-layout: fixed' ) );
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
