<?php
$w			= (object) $words['editInfo'];

$helperAge	= new View_Helper_TimePhraser( $env );

$roleMap	= [];
foreach( $roles as $role )
	$roleMap[$role->roleId] = $role;

$facts		= [];
$facts[]	= array(
	'label'	=> $w->labelRegisteredAt,
	'value'	=> $helperAge->convert( $user->createdAt, TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix )
);
if( $user->loggedAt ){
	$loggedAt	= $helperAge->convert( $user->loggedAt );
	$facts[]	= array(
		'label'	=> $w->labelLoggedAt,
		'value'	=> $helperAge->convert( $user->loggedAt, TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix )
	);
}
if( $user->activeAt ){
	$activeAt	= $helperAge->convert( $user->activeAt );
	$facts[]	= array(
		'label'	=> $w->labelActiveAt,
		'value'	=> $helperAge->convert( $user->activeAt, TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix )
	);
}

if( !empty( $projects ) ){
	$list	= [];
	foreach( $projects as $project ){
		$label	= $project->title;
		if( !in_array( (int) $project->status, array( 0, 1, 2, 3 ) ) )
			$label	= UI_HTML_Tag::create( 'del', $label );
		$url	= './manage/project/edit/'.$project->projectId;
		$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url, 'class' => 'project project-list-item' ) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$projects	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'projects project-list' ) );
	$facts[]	= array(
		'label'	=> 'Projekte',
		'value'	=> $projects,
		'style'	=> 'max-height: 200px; overflow-y: auto',
//		'class'	=> 'fact-list-definition-multiple',
	);
}

foreach( $facts as $nr => $fact ){
	$fact['class']	= !empty( $fact['class'] ) ? $fact['class'] : NULL;
	$fact['style']	= !empty( $fact['style'] ) ? $fact['style'] : NULL;
	$term			= UI_HTML_Tag::create( 'dt', $fact['label'] );
	$definition		= UI_HTML_Tag::create( 'dd', $fact['value'], array(
		'class'		=> $fact['class'],
		'style'		=> $fact['style']
	) );
	$facts[$nr]		= $term.$definition;
}
$facts	= UI_HTML_Tag::create( 'dl', join( $facts ) );

return '
<div class="content-panel content-panel-info">
	<h4>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				<dl>
					<dt>'.$w->labelRole.'</dt>
					<dd><span class="role role'.$user->role->roleId.'">'.$user->role->title.'</span></dd>
					<dt>'.$w->labelStatus.'</dt>
					<dd><span class="user-status status'.$user->status.'">'.$words['status'][$user->status].'</span></dd>
				</dl>
				'.$facts.'
			</div>
		</div>
	</div>
</div>';
?>
