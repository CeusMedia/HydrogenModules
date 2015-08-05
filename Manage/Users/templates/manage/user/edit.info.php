 <?php

$w  = (object) $words['editInfo'];

$roleMap	= array();
foreach( $roles as $role )
	$roleMap[$role->roleId] = $role;

$facts		= array();
$helper		= new View_Helper_TimePhraser( $env );
$facts[]	= array(
	'label'	=> $w->labelRegisteredAt,
	'value'	=> $helper->convert( $user->createdAt, TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix )
);
if( $user->loggedAt ){
	$loggedAt	= $helper->convert( $user->loggedAt );
	$facts[]	= array(
		'label'	=> $w->labelLoggedAt,
		'value'	=> $helper->convert( $user->loggedAt, TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix )
	);
}
if( $user->activeAt ){
	$activeAt	= $helper->convert( $user->activeAt );
	$facts[]	= array(
		'label'	=> $w->labelActiveAt,
		'value'	=> $helper->convert( $user->activeAt, TRUE, $w->timePhrasePrefix, $w->timePhraseSuffix )
	);
}
if( !empty( $projects ) ){
	$list	= array();
	foreach( $projects as $project ){
		$url	= './manage/project/edit/'.$project->projectId;
		$link	= UI_HTML_Tag::create( 'a', $project->title, array( 'href' => $url, 'class' => 'project' ) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$projects	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'projects' ) );
	$facts[]	= array(
		'label'	=> 'Projekte',
		'value'	=> $projects
	);
}

foreach( $facts as $nr => $fact )
	$facts[$nr]	= UI_HTML_Tag::create( 'dt', $fact['label'] ).UI_HTML_Tag::create( 'dd', $fact['value'] );
$facts	= UI_HTML_Tag::create( 'dl', join( $facts ) );

return '
<div class="content-panel">
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
				<hr>
				'.$facts.'
			</div>
		</div>
	</div>
</div>';
?>
