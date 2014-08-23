<?php
/*
$list	= '<div class="muted"><em><small>Keine Applikationen angemeldet.</small></em></div><br/>';
if( $applications ){
	$trClasses	= array( -2 => 'error', -1 => 'error', 0 => 'warning', 1 => 'success', 2 => 'info' );
	$list		= array();
	foreach( $applications as $application ){
		$urlEdit	= './oauth/application/view/'.$application->oauthApplicationId;
		$label		= UI_HTML_Tag::create( 'big', $application->title );
		$link		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $urlEdit ) );
		$clientId	= UI_HTML_Tag::create( 'small', 'Client-ID: '.$application->clientId, array( 'class'=> 'muted' ) );
		$createdAt	= date( 'd.m.Y H:i', $application->createdAt );
		$modifiedAt	= $application->modifiedAt ? date( 'd.m.Y H:i', $application->modifiedAt ) : '-';
		$type		= $words['types'][$application->type];
		$status		= $words['states'][$application->status];
		$trClass	= 
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link.'<br/>'.$clientId ),
			UI_HTML_Tag::create( 'td', $type.'<br/>'.$status ),
			UI_HTML_Tag::create( 'td', $createdAt.'<br/>'.$modifiedAt ),
		), array( 'class' => $trClasses[(int) $application->status]) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "30%", "40%", "15%", "15%" );
	$theads		= UI_HTML_Elements::TableHeads( array(
		"Applikation",
		"Beschreibung",
		"Zustand",
		"erstellt / verändert"
	) );
	$thead		= UI_HTML_Tag::create( 'thead', $theads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
		'class'	=> 'table table-striped'
	) );
}
*/

$iconsStatus	= array(
	-1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-removed' ) ),
	0	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-stop' ) ),
	1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-play' ) )
);
$iconsType	= array(
	0	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-world' ) ),
	1	=> UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-lock' ) )
);

$list	= '<div class="muted"><em><small>Keine Applikationen angemeldet.</small></em></div><br/>';
if( $applications ){
	$list		= array();
	foreach( $applications as $application ){
		$urlEdit	= './oauth/application/view/'.$application->oauthApplicationId;
		$label		= UI_HTML_Tag::create( 'big', $application->title );
		$link		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $urlEdit ) );
		$createdAt	= date( 'd.m.Y H:i', $application->createdAt );
		$modifiedAt	= $application->modifiedAt ? date( 'd.m.Y H:i', $application->modifiedAt ) : '-';

		$typeLabel		= $words['types'][$application->type];
		$typeIcon		= $iconsType[$application->type];

		$statusLabel	= $words['states'][$application->status];
		$statusIcon		= $iconsStatus[$application->status];
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link.'<br/><small class="muted">'.$application->url.'</small>' ),
			UI_HTML_Tag::create( 'td', $typeIcon.' '.$typeLabel ),
			UI_HTML_Tag::create( 'td', $statusIcon.' '.$statusLabel ),
			UI_HTML_Tag::create( 'td', $createdAt.'<br/>'.$modifiedAt ),
		), array( 'class' => NULL ) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "50%", "15%", "15%", "20%" );
	$theads		= UI_HTML_Elements::TableHeads( array(
		"Applikation",
		"Vertraulichkeit",
		"Zustand",
		"erstellt / verändert"
	) );
	$thead		= UI_HTML_Tag::create( 'thead', $theads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
		'class'	=> 'table table-striped'
	) );
}


$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );
$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' hinzufügen', array(
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
?>
