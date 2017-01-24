<?php


function renderUserLabel( $user ){
	$iconUser	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-user' ) );
	$spanClass	= 'user role role'.$user->roleId;
	$fullname	= $user->firstname.' '.$user->surname;
	$username	= UI_HTML_Tag::create( 'abbr', $user->username, array( 'title' => $fullname ) );
	$label		= $iconUser.'&nbsp;'.$username;
	return UI_HTML_Tag::create( 'span', $label, array( 'class' => $spanClass ) );
}

$phraser	= new View_Helper_TimePhraser( $env );

$infos		= array();

if( isset( $mission->creator ) )
	$infos['creator']	= array(
		'label'	=> 'Erstellt von',
		'value'	=> renderUserLabel( $mission->creator ),
	);

$typeIcons	= array(
	0 => UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-thumb-tack' ) ),
	1 => UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-clock-o' ) ),
);
$infos['type']	= array(
	'label'	=> 'Missionstyp',
	'value'	=> '<span class="mission mission-type type'.$mission->type.'">'.$typeIcons[$mission->type].'&nbsp;'.$words['types'][$mission->type].'</span>'
);

$typeIcons	= array(
	0 => UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-thumb-tack' ) ),
	1 => UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-clock-o' ) ),
);
$infos['type']	= array(
	'label'	=> 'Typ',
	'value'	=> '<span class="mission mission-type type'.$mission->type.'">'.$typeIcons[$mission->type].'&nbsp;'.$words['types'][$mission->type].'</span>'
);

$infos['state']	= array(
	'label'	=> 'aktueller Zustand',
	'value'	=> '<span class="mission mission-status status'.$mission->status.'">'.$words['states'][$mission->status].'</span>'
);

$infos['priority']	= array(
	'label'	=> 'Priorität',
	'value'	=> '<span class="mission mission-priority priority'.$mission->priority.'">'.$words['priorities'][$mission->priority].'</span>'
);

if( isset( $mission->worker ) )
	$infos['worker']	= array(
		'label'	=> 'Zugewiesen an',
		'value'	=> renderUserLabel( $mission->worker ),
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
if( isset( $mission->modifier ) )
	$infos['modifier']	= array(
		'label'	=> 'Geändert von',
		'value'	=> renderUserLabel( $mission->modifier ),
	);

if( count( $missionUsers ) > 1 ){
	$list	= array();
	$iconUser	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-user' ) );
	foreach( $missionUsers as $user ){
		$list[$user->username]	= renderUserLabel( $user );
	}

	ksort( $list, defined( 'SORT_FLAG_CASE' ) ? SORT_FLAG_CASE : SORT_REGULAR );
	$infos['list-users-viewers']	= array(
		'label'	=> 'Sichtbar für',
		'value'	=> $list
	);
}

$infos['links']	= array(
	'label'	=> '<a href="./work/mission/'.$mission->missionId.'" target="_blank">Link</a>',
	'value'	=> '<a href="./work/mission/'.$mission->missionId.'" class="btn btn-mini" target="_blank">in neuem Tab öffnen</a>'
);

/*
$model		= new Model_Mission_Change( $env );
$changes	= $model->getAllByIndex( 'missionId', $mission->missionId );
//print_m( $mission );
//print_m( $model->getColumns() );
print_m( $changes );
*/

/*
if( isset( $mission->creator ) )
	$infos[]	= array(
		'label'	=> '',
		'value'	=> ''
	);
if( isset( $mission->creator ) )
	$infos[]	= array(
		'label'	=> '',
		'value'	=> ''
	);
*/

$factInfoKeys	= array(
	'type',
	'state',
	'priority',
	'creator',
	'date-creation',
	'modifier',
	'date-modification',
	'worker',
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
foreach( $facts as $fact ){
	$class	= NULL;
	if( is_array( $fact->value ) ){
		if( count( $fact->value ) > 5)
			$class	= 'max-y';
		$fact->value	= join( "<br/>", $fact->value );
	}
	$term	= UI_HTML_Tag::create( 'dt', $fact->label );
	$def	= UI_HTML_Tag::create( 'dd', $fact->value, array( 'class' => $class ) );
	$list[]	= $term.$def;
}
$list		= UI_HTML_Tag::create( 'dl', join( $list ), array( 'class' => 'dl-horizontal' ) );

return '
<div class="content-panel content-panel-info">
	<h3>Informationen</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
?>
