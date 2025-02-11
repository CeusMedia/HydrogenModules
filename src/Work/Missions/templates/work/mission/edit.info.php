<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array $words */
/** @var object[] $projects */
/** @var object[] $missionUsers */
/** @var Entity_Mission $mission */

/**
 * @param $user
 * @return string
 */
function renderUserLabel( $user ): string
{
	$iconUser	= HtmlTag::create( 'i', '', ['class' => 'icon-user'] );
	$spanClass	= 'user role role'.$user->roleId;
	$fullname	= $user->firstname.' '.$user->surname;
	$username	= HtmlTag::create( 'abbr', $user->username, ['title' => $fullname] );
	$label		= $iconUser.'&nbsp;'.$username;
	return HtmlTag::create( 'span', $label, ['class' => $spanClass] );
}

$phraser	= new View_Helper_TimePhraser( $env );

$infos		= [];

if( isset( $mission->creator ) )
	$infos['creator']	= [
		'label'	=> 'Erstellt von',
		'value'	=> renderUserLabel( $mission->creator ),
	];

$typeIcons	= [
	0 => HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-thumb-tack'] ),
	1 => HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-clock-o'] ),
];
$infos['type']	= [
	'label'	=> 'Missionstyp',
	'value'	=> '<span class="mission mission-type type'.$mission->type.'">'.$typeIcons[$mission->type].'&nbsp;'.$words['types'][$mission->type].'</span>'
];

$typeIcons	= [
	0 => HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-thumb-tack'] ),
	1 => HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-clock-o'] ),
];
$infos['type']	= [
	'label'	=> 'Typ',
	'value'	=> '<span class="mission mission-type type'.$mission->type.'">'.$typeIcons[$mission->type].'&nbsp;'.$words['types'][$mission->type].'</span>'
];

$infos['state']	= [
	'label'	=> 'aktueller Zustand',
	'value'	=> '<span class="mission mission-status status'.$mission->status.'">'.$words['states'][$mission->status].'</span>'
];

$infos['priority']	= [
	'label'	=> 'Priorität',
	'value'	=> '<span class="mission mission-priority priority'.$mission->priority.'">'.$words['priorities'][$mission->priority].'</span>'
];

if( isset( $mission->worker ) )
	$infos['worker']	= [
		'label'	=> 'Zugewiesen an',
		'value'	=> renderUserLabel( $mission->worker ),
	];

if( isset( $mission->createdAt ) )
	$infos['date-creation']	= [
		'label'	=> 'Erstellung',
		'value'	=> '<span class="date">vor '.$phraser->convert( $mission->createdAt, TRUE ).'</span>'
	];

if( isset( $mission->modifiedAt ) )
	$infos['date-modification']	= [
		'label'	=> 'Zuletzt geändert',
		'value'	=> '<span class="date">vor '.$phraser->convert( $mission->modifiedAt, TRUE ).'</span>'
	];
if( isset( $mission->modifier ) )
	$infos['modifier']	= [
		'label'	=> 'Geändert von',
		'value'	=> renderUserLabel( $mission->modifier ),
	];
if( isset( $mission->projectId ) ){
	$value		= $projects[$mission->projectId]->title;
	if( $env->getAcl()->has( 'manage/project', 'view' ) ){
		$url	= './manage/project/view/'.$mission->projectId;
		$value	= HtmlTag::create( 'a', $value, ['href' => $url] );
	}
	$infos['project']	= [
		'label'	=> 'Projekt',
		'value'	=> $value,
	];
}

if( count( $missionUsers ) > 1 ){
	$list	= [];
	$iconUser	= HtmlTag::create( 'i', '', ['class' => 'icon-user'] );
	foreach( $missionUsers as $user ){
		$list[$user->username]	= renderUserLabel( $user );
	}

	ksort( $list, defined( 'SORT_FLAG_CASE' ) ? SORT_FLAG_CASE : SORT_REGULAR );
	$infos['list-users-viewers']	= [
		'label'	=> 'Sichtbar für',
		'value'	=> $list
	];
}

$infos['links']	= [
	'label'	=> '<a href="./work/mission/'.$mission->missionId.'" target="_blank">Link</a>',
	'value'	=> '<a href="./work/mission/'.$mission->missionId.'" class="btn btn-mini" target="_blank">in neuem Tab öffnen</a>'
];

/*
$model		= new Model_Mission_Change( $env );
$changes	= $model->getAllByIndex( 'missionId', $mission->missionId );
//print_m( $mission );
//print_m( $model->getColumns() );
print_m( $changes );
*/

/*
if( isset( $mission->creator ) )
	$infos[]	= [
		'label'	=> '',
		'value'	=> ''
	];
if( isset( $mission->creator ) )
	$infos[]	= [
		'label'	=> '',
		'value'	=> ''
	];
*/

$factInfoKeys	= [
	'type',
	'state',
	'priority',
	'creator',
	'date-creation',
	'modifier',
	'date-modification',
	'worker',
	'list-users-viewers',
	'project',
	'links',
];

$facts	= [];
foreach( $factInfoKeys as $key ){
	if( isset( $infos[$key] ) )
		$facts[]	= (object) $infos[$key];
}

if( !count( $facts ) )
	return '';

$list		= [];
foreach( $facts as $fact ){
	$class	= NULL;
	if( is_array( $fact->value ) ){
		if( count( $fact->value ) > 5)
			$class	= 'max-y';
		$fact->value	= join( "<br/>", $fact->value );
	}
	$term	= HtmlTag::create( 'dt', $fact->label );
	$def	= HtmlTag::create( 'dd', $fact->value, ['class' => $class] );
	$list[]	= $term.$def;
}
$list		= HtmlTag::create( 'dl', join( $list ), ['class' => 'dl-horizontal'] );

return '
<div class="content-panel content-panel-info">
	<h3>Informationen</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
