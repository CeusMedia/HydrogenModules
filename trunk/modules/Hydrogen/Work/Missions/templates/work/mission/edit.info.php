<?php


$phraser	= new View_Helper_TimePhraser( $env );

$infos	= array();

if( isset( $mission->owner ) )
	$infos['owner']	= array(
		'label'	=> 'Erstellt von',
		'value'	=> '<span class="user role role'.$mission->owner->roleId.'">'.$mission->owner->username.'</span>'
	);

$infos['state']	= array(
	'label'	=> 'aktueller Zustand',
	'value'	=> '<span class="mission-status status'.$mission->status.'">'.$words['states'][$mission->status].'</span>'
);

$infos['priority']	= array(
	'label'	=> 'Priorität',
	'value'	=> '<span class="mission-priority priority'.$mission->priority.'">'.$words['priorities'][$mission->priority].'</span>'
);

$infos['type']	= array(
	'label'	=> 'Missionstyp',
	'value'	=> '<span class="mission-type type'.$mission->type.'">'.$words['types'][$mission->type].'</span>'
);

if( isset( $mission->worker ) )
	$infos['worker']	= array(
		'label'	=> 'Zugewiesen an',
		'value'	=> '<span class="user role role'.$mission->worker->roleId.'">'.$mission->worker->username.'</span>'
	);

if( isset( $mission->createdAt ) )
	$infos['date-creation']	= array(
		'label'	=> 'Erstellung',
		'value'	=> '<span class="date">vor '.$phraser->convert( $mission->createdAt, TRUE ).'</span>'
	);

if( isset( $mission->modifiedAt ) )
	$infos['date-modification']	= array(
		'label'	=> 'Zuletzt geändert',
		'value'	=> '<span class="date">vor '.$phraser->convert( $mission->modifiedAt, TRUE ).'</span>'
	);

if( count( $missionUsers ) > 1 ){
	$list	= array();
	$iconUser	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-user' ) );
	foreach( $missionUsers as $user )
//		$list[]	= UI_HTML_Tag::create( 'span', $user->username, array( 'class' => 'user role role'.$user->roleId ) );
		$list[]	= UI_HTML_Tag::create( 'span', $iconUser.' '.$user->username, array( 'class' => 'user role role'.$user->roleId ) );
	$infos['list-users-viewers']	= array(
		'label'	=> 'Sichtbar für',
		'value'	=> join( '<br/>', $list )
	);
}

$infos['links']	= array(
	'label'	=> '<a href="./work/mission/'.$mission->missionId.'" target="_blank">Link</a>',
	'value'	=> '<a href="./work/mission/'.$mission->missionId.'" class="btn btn-mini" target="_blank">in neuem Tab öffnen</a>'
);

/*
if( isset( $mission->owner ) )
	$infos[]	= array(
		'label'	=> '',
		'value'	=> ''
	);
if( isset( $mission->owner ) )
	$infos[]	= array(
		'label'	=> '',
		'value'	=> ''
	);
*/

$factInfoKeys	= array(
	'owner',
	'state',
	'priority',
	'type',
	'worker',
	'date-modification',
	'date-creation',
	'list-users-viewers',
	'links',
);

$facts	= array();
foreach( $factInfoKeys as $key ){
	if( isset( $infos[$key] ) )
		$facts[]	= (object) $infos[$key];
}

if( !count( $facts ) )
	return '';

$list		= array();
foreach( $facts as $fact )
	$list[]	= UI_HTML_Tag::create( 'dt', $fact->label ).UI_HTML_Tag::create( 'dd', $fact->value );
$list		= UI_HTML_Tag::create( 'dl', join( $list ), array( 'class' => 'dl-horizontal' ) );

return '
<div class="content-panel content-panel-form">
	<h3>Informationen</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
?>
