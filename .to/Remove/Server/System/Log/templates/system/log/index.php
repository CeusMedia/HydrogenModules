<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconView	= HtmlTag::create( 'i', '', array( 'class' => 'icon-eye-open not-icon-white' ) );
$iconRemove	= HtmlTag::create( 'i', '', array( 'class' => 'icon-trash icon-white' ) );

$list	= '<div class="muted"><em><small>No exceptions logged.</small></em></div>';
if( $exceptions ){
	$list	= [];
	foreach( $exceptions as $nr => $exception ){

		$link	= HtmlTag::create( 'a', $exception->message, array( 'href' => './system/log/view/'.$exception->id ) );
		$date	= date( 'Y.m.d', $exception->timestamp );
		$time	= date( 'H:i:s', $exception->timestamp );

		$buttons	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'a', $iconView, array(
				'class'	=> 'btn btn-mini not-btn-info',
				'href'	=> './system/log/view/'.$exception->id
			) ),
			HtmlTag::create( 'a', $iconRemove, array(
				'class'	=> 'btn btn-mini btn-danger',
				'href'	=> './system/log/remove/'.$exception->id
			) ),
		), array( 'class' => 'btn-group' ) );

		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link, array( 'class' => 'autocut' ) ),
			HtmlTag::create( 'td', $date.'&nbsp;<small class="muted">'.$time.'</small>' ),
			HtmlTag::create( 'td', $buttons ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( "", "150px", "100px" );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$tbody, array( 'class' => 'table table-striped table-condensed', 'style' => 'table-layout: fixed' ) );
}

$pagination	= new \CeusMedia\Bootstrap\PageControl( './system/log', $page, ceil( $total / $limit ) );
$pagination	= $pagination->render();

$panelList	= '
		<div class="content-panel">
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
