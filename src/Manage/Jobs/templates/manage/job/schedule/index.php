<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconActivate	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-toggle-on'] );
$iconDeactivate	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-toggle-off'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$buttonAdd		= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], [
	'href'	=> './manage/job/schedule/add',
	'class'	=> 'btn btn-success',
] );

//return print_m( $allDefinedJobs, NULL, NULL, TRUE );

$table	= HtmlTag::create( 'div', 'Noch keine Jobs geplant.', ['class' => 'alert'] );

if( $scheduledJobs ){
	$rows	= [];

	foreach( $scheduledJobs as $item ){
		$buttonView		= HtmlTag::create( 'a', $iconView, [
			'href'		=> './manage/job/schedule/view/'.$item->jobScheduleId,
			'class'		=> 'btn btn-info btn-small',
			'title'		=> 'Details anzeigen',
		] );
		$buttonEdit		= HtmlTag::create( 'a', $iconEdit, [
			'href'		=> './manage/job/schedule/edit/'.$item->jobScheduleId,
			'class'		=> 'btn not-btn-info btn-small',
			'title'		=> 'Eintrag bearbeiten',
		] );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'href'		=> './manage/job/schedule/remove/'.$item->jobScheduleId,
			'class'		=> 'btn btn-inverse btn-small',
			'title'		=> 'Eintrag entfernen',
		] );
		$buttonStatus	= HtmlTag::create( 'a', $iconActivate, [
			'href'		=> './manage/job/schedule/setStatus/'.$item->jobScheduleId.'/1',
			'class'		=> 'btn btn-success btn-small',
			'title'		=> 'aktivieren',
		] );
		if( $item->status == Model_Job_Schedule::STATUS_ENABLED )
			$buttonStatus	= HtmlTag::create( 'a', $iconDeactivate, [
				'href'		=> './manage/job/schedule/setStatus/'.$item->jobScheduleId.'/0',
				'class'		=> 'btn btn-warning btn-small',
				'title'		=> 'deaktivieren',
			 ] );

		$buttons	= HtmlTag::create( 'div', [
			$buttonView,
			$buttonEdit,
			$buttonStatus,
			$buttonRemove
		], ['class' => 'btn-group'] );
		$status	= HtmlTag::create( 'span', 'aktiv', ['class' => 'badge badge-success'] );
		if( $item->status == Model_Job_Schedule::STATUS_DISABLED )
			$status	= HtmlTag::create( 'span', 'deaktiviert', ['class' => 'badge badge-warning'] );

		$type	= $words['types'][$item->type];

		$expression	= $item->expression;
		if( (int) $item->type === Model_Job_Schedule::TYPE_CRON )
			$expression	= HtmlTag::create( 'abbr', $expression, ['title' => \Lorisleiva\CronTranslator\CronTranslator::translate( $expression )] );


		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $item->definition->identifier, ['class' => ''] ),
			HtmlTag::create( 'td', $type, ['class' => ''] ),
			HtmlTag::create( 'td', HtmlTag::create( 'tt', $expression ), ['class' => ''] ),
			HtmlTag::create( 'td', $item->lastRunAt ? date( 'd.m.Y H:i', $item->lastRunAt ) : '-', ['class' => ''] ),
			HtmlTag::create( 'td', $status, ['class' => ''] ),
			HtmlTag::create( 'td', $buttons, ['class' => ''] ),
		) );
	}
	$cols	= HtmlElements::ColumnGroup( '', '180px', '140px', '140px', '140px', '140px' );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( ['Job ID / Title', 'Typ/Format', 'Ausführung', 'letzter Lauf', 'Zustand', ''] ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$cols, $thead, $tbody], ['class' => 'table'] );
}

$tabs	= View_Manage_Job::renderTabs( $env, 'schedule' );

return $tabs.HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $words['index']['heading'] ),
	HtmlTag::create( 'div', array(
		$table,
		HtmlTag::create( 'div', [
			$buttonAdd,
		], ['class' => 'buttonbar'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

//return print_m( $schedule, NULL, NULL, TRUE );
