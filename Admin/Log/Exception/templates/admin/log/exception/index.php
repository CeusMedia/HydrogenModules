<?php

$iconView	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-eye-open not-icon-white' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );

$selectInstance	= '';
if( count( $instances ) > 1 ){
	$optInstance	= [];
	foreach( $instances as $instanceKey => $instanceData )
		$optInstance[$instanceKey]	= $instanceData->title;
	$optInstance	= UI_HTML_Elements::Options( $optInstance, $currentInstance );
	$selectInstance	= UI_HTML_Tag::create( 'select', $optInstance, array(
		'oninput'	=> 'document.location.href = "./admin/log/exception/setInstance/" + jQuery(this).val();',
		'class'		=> '',
		'style'		=> 'width: 100%',
	) );

}

$list	= '<div class="muted"><em><small>No exceptions logged.</small></em></div>';
if( $exceptions ){
	$list	= [];
	foreach( $exceptions as $nr => $exception ){
//print_m($exception);die;
		$link	= UI_HTML_Tag::create( 'a', $exception->message, array( 'href' => './admin/log/exception/view/'.$exception->exceptionId ) );
		$date	= date( 'Y.m.d', $exception->createdAt );
		$time	= date( 'H:i:s', $exception->createdAt );

		$buttons	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'a', $iconView, array(
				'class'	=> 'btn btn-mini not-btn-info',
				'href'	=> './admin/log/exception/view/'.$exception->exceptionId
			) ),
			UI_HTML_Tag::create( 'a', $iconRemove, array(
				'class'	=> 'btn btn-mini btn-danger',
				'href'	=> './admin/log/exception/remove/'.$exception->exceptionId
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

$pagination	= new \CeusMedia\Bootstrap\PageControl( './admin/log/exception', $page, ceil( $total / $limit ) );
$pagination	= $pagination->render();

$panelList	= '
		<div class="content-panel" style="position: relative">
			<div style="position: absolute; right: 1em; top: 0.65em; width: 150px;">
				'.$selectInstance.'
			</div>
			<h3>Exceptions</h3>
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
