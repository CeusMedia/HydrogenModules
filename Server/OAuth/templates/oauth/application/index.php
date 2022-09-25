<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/*
$list	= '<div class="muted"><em><small>Keine Applikationen angemeldet.</small></em></div><br/>';
if( $applications ){
	$trClasses	= array( -2 => 'error', -1 => 'error', 0 => 'warning', 1 => 'success', 2 => 'info' );
	$list		= [];
	foreach( $applications as $application ){
		$urlEdit	= './oauth/application/view/'.$application->oauthApplicationId;
		$label		= HtmlTag::create( 'big', $application->title );
		$link		= HtmlTag::create( 'a', $label, array( 'href' => $urlEdit ) );
		$clientId	= HtmlTag::create( 'small', 'Client-ID: '.$application->clientId, array( 'class'=> 'muted' ) );
		$createdAt	= date( 'd.m.Y H:i', $application->createdAt );
		$modifiedAt	= $application->modifiedAt ? date( 'd.m.Y H:i', $application->modifiedAt ) : '-';
		$type		= $words['types'][$application->type];
		$status		= $words['states'][$application->status];
		$trClass	= 
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link.'<br/>'.$clientId ),
			HtmlTag::create( 'td', $type.'<br/>'.$status ),
			HtmlTag::create( 'td', $createdAt.'<br/>'.$modifiedAt ),
		), array( 'class' => $trClasses[(int) $application->status]) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "30%", "40%", "15%", "15%" );
	$theads		= UI_HTML_Elements::TableHeads( array(
		"Applikation",
		"Beschreibung",
		"Zustand",
		"erstellt / verändert"
	) );
	$thead		= HtmlTag::create( 'thead', $theads );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array(
		'class'	=> 'table table-striped'
	) );
}
*/

$iconAdd	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$iconsStatus	= array(
	-1	=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fw-trash' ) ),
	0	=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-stop' ) ),
	1	=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-play' ) )
);
$iconsType	= array(
	0	=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-world' ) ),
	1	=> HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-lock' ) )
);

$list	= '<div class="muted"><em><small>Keine Applikationen angemeldet.</small></em></div><br/>';
if( $applications ){
	$list		= [];
	foreach( $applications as $application ){
		$urlEdit	= './oauth/application/view/'.$application->oauthApplicationId;
		$label		= HtmlTag::create( 'big', $application->title );
		$link		= HtmlTag::create( 'a', $label, array( 'href' => $urlEdit ) );
		$createdAt	= date( 'd.m.Y H:i', $application->createdAt );
		$modifiedAt	= $application->modifiedAt ? date( 'd.m.Y H:i', $application->modifiedAt ) : '-';

		$typeLabel		= $words['types'][$application->type];
		$typeIcon		= $iconsType[$application->type];

		$statusLabel	= $words['states'][$application->status];
		$statusIcon		= $iconsStatus[$application->status];
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link.'<br/><small class="muted">'.$application->url.'</small>' ),
			HtmlTag::create( 'td', $typeIcon.' '.$typeLabel ),
			HtmlTag::create( 'td', $statusIcon.' '.$statusLabel ),
			HtmlTag::create( 'td', $createdAt.'<br/>'.$modifiedAt ),
		), array( 'class' => NULL ) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( '50%', '15%', '15%', '20%' );
	$theads		= UI_HTML_Elements::TableHeads( array(
		'Applikation',
		'Vertraulichkeit',
		'Zustand',
		'erstellt / verändert',
	) );
	$thead		= HtmlTag::create( 'thead', $theads );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array(
		'class'	=> 'table table-striped'
	) );
}


$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' hinzufügen', array(
	'class'	=> 'btn btn-primary btn-small',
	'href'	=> './oauth/application/add',
) );
return '
<h2 class="muted">OAuth-Server</h2>
<div class="content-panel">
	<div class="content-panel-inner">
		<h3>Applikationen</h3>
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
