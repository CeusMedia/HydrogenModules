<?php

$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-toggle-on' ) );
$iconDeactivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-toggle-off' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$buttonAdd		= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], array(
	'href'	=> './manage/job/schedule/add',
	'class'	=> 'btn btn-success',
) );

//return print_m( $allDefinedJobs, NULL, NULL, TRUE );

$table	= UI_HTML_Tag::create( 'div', 'Noch keine Jobs geplant.', array( 'class' => 'alert' ) );

if( $scheduledJobs ){
	$rows	= [];

	foreach( $scheduledJobs as $item ){
		$buttonView		= UI_HTML_Tag::create( 'a', $iconView, array(
			'href'		=> './manage/job/schedule/view/'.$item->jobScheduleId,
			'class'		=> 'btn btn-info btn-small',
			'title'		=> 'Details anzeigen',
		) );
		$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit, array(
			'href'		=> './manage/job/schedule/edit/'.$item->jobScheduleId,
			'class'		=> 'btn not-btn-info btn-small',
			'title'		=> 'Eintrag bearbeiten',
		) );
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'		=> './manage/job/schedule/remove/'.$item->jobScheduleId,
			'class'		=> 'btn btn-inverse btn-small',
			'title'		=> 'Eintrag entfernen',
		) );
		$buttonStatus	= UI_HTML_Tag::create( 'a', $iconActivate, array(
			'href'		=> './manage/job/schedule/setStatus/'.$item->jobScheduleId.'/1',
			'class'		=> 'btn btn-success btn-small',
			'title'		=> 'aktivieren',
		) );
		if( $item->status == Model_Job_Schedule::STATUS_ENABLED )
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconDeactivate, array(
				'href'		=> './manage/job/schedule/setStatus/'.$item->jobScheduleId.'/0',
				'class'		=> 'btn btn-warning btn-small',
				'title'		=> 'deaktivieren',
			 ) );

		$buttons	= UI_HTML_Tag::create( 'div', array(
			$buttonView,
			$buttonEdit,
			$buttonStatus,
			$buttonRemove
		), array( 'class' => 'btn-group' ) );
		$status	= UI_HTML_Tag::create( 'span', 'aktiv', array( 'class' => 'badge badge-success' ) );
		if( $item->status == Model_Job_Schedule::STATUS_DISABLED )
			$status	= UI_HTML_Tag::create( 'span', 'deaktiviert', array( 'class' => 'badge badge-warning' ) );

		$type	= $words['types'][$item->type];

		$expression	= $item->expression;
		if( (int) $item->type === Model_Job_Schedule::TYPE_CRON )
			$expression	= UI_HTML_Tag::create( 'abbr', $expression, array( 'title' => \Lorisleiva\CronTranslator\CronTranslator::translate( $expression ) ) );


		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $item->definition->identifier, array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $type, array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', UI_HTML_Tag::create( 'tt', $expression ), array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $item->lastRunAt ? date( 'd.m.Y H:i', $item->lastRunAt ) : '-', array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $status, array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'class' => '' ) ),
		) );
	}
	$cols	= UI_HTML_Elements::ColumnGroup( '', '180px', '140px', '140px', '140px', '140px' );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Job ID / Title', 'Typ/Format', 'Ausführung', 'letzter Lauf', 'Zustand', '' ) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', array( $cols, $thead, $tbody ), array( 'class' => 'table' ) );
}

$tabs	= View_Manage_Job::renderTabs( $env, 'schedule' );

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
