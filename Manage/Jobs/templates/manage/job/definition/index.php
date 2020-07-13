<?php

$statusLabels	= $wordsGeneral['job-status'];
//print_m($definitions);die;

$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-toggle-on' ) );
$iconDeactivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-toggle-off' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], array(
	'href'	=> './manage/job/definition/add',
	'class'	=> 'btn btn-success',
) );

$modes	= array(
	0	=> 'UNDEFINED',
	1	=> 'SINGLE',
	2	=> 'MULTIPLE',
	3	=> 'EXCLUSIVE',
);
//return print_m( $allDefinedJobs, NULL, NULL, TRUE );



$table	= UI_HTML_Tag::create( 'div', 'Noch keine Jobs geplant.', array( 'class' => 'alert' ) );

if( $definitions ){
	$helperTime		= new View_Helper_TimePhraser( $env );
	$helperTime->setTemplate( '%s ago' );
	$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );

	$rows	= array();
	foreach( $definitions as $item ){
		$buttonView		= UI_HTML_Tag::create( 'a', $iconView, array(
			'href'		=> './manage/job/definition/view/'.$item->jobDefinitionId,
			'class'		=> 'btn btn-info btn-small',
			'title'		=> 'Details anzeigen',
		) );
		$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit, array(
			'href'		=> './manage/job/definition/edit/'.$item->jobDefinitionId,
			'class'		=> 'btn not-btn-info btn-small',
			'title'		=> 'Eintrag bearbeiten',
		) );
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'		=> './manage/job/definition/remove/'.$item->jobDefinitionId,
			'class'		=> 'btn btn-inverse btn-small',
			'title'		=> 'Eintrag entfernen',
		) );
		$buttonStatus	= UI_HTML_Tag::create( 'a', $iconActivate, array(
			'href'		=> './manage/job/definition/setStatus/'.$item->jobDefinitionId.'/1',
			'class'		=> 'btn btn-success btn-small',
			'title'		=> 'aktivieren',
		) );
		if( $item->status == Model_Job_Definition::STATUS_ENABLED )
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconDeactivate, array(
				'href'		=> './manage/job/definition/setStatus/'.$item->jobDefinitionId.'/0',
				'class'		=> 'btn btn-warning btn-small',
				'title'		=> 'deaktivieren',
			 ) );

		$buttons	= UI_HTML_Tag::create( 'div', array(
			$buttonView,
			$buttonEdit,
			$buttonStatus,
			$buttonRemove
		), array( 'class' => 'btn-group' ) );
		$status	= UI_HTML_Tag::create( 'span', $statusLabels[$item->status], array( 'class' => 'badge badge-success' ) );
		if( $item->status == Model_Job_Definition::STATUS_DISABLED )
			$status	= UI_HTML_Tag::create( 'span', $statusLabels[$item->status], array( 'class' => 'badge badge-warning' ) );
		$mode	= $modes[$item->mode];

		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $item->identifier, array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', '<span class="text-success">'.$item->runs.'</span> / <span class="text-error">'.$item->fails.'</span>', array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $mode, array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $status, array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $item->lastRunAt ? $helperTime->setTimestamp( $item->lastRunAt )->render() : '-', array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'class' => '' ) ),
		) );
	}
	$cols	= UI_HTML_Elements::ColumnGroup( '', '100px', '120px', '120px', '140px', '140px' );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Identifier', 'Runs / Fails', 'Modus', 'Zustand', 'letzte Ausführung', '' ) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', array( $cols, $thead, $tbody ), array( 'class' => 'table' ) );
}

$tabs	= View_Manage_Job::renderTabs( $env, 'definition' );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $words['index']['heading'] ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', array(
			$buttonAdd,
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

//return print_m( $schedule, NULL, NULL, TRUE );
