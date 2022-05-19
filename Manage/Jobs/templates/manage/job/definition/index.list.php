<?php

$helperAttribute	= new View_Helper_Job_Attribute( $env );

$statusLabels	= $wordsGeneral['job-definition-statuses'];
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

$table	= UI_HTML_Tag::create( 'div', 'Noch keine Jobs geplant.', array( 'class' => 'alert' ) );

if( $definitions ){
	$helperTime		= new View_Helper_TimePhraser( $env );
	$helperTime->setTemplate( '%s ago' );
	$helperTime->setMode( View_Helper_TimePhraser::MODE_BREAK );

	$rows	= [];
	foreach( $definitions as $item ){
		$helperAttribute->setObject( $item );
		$parts	= explode( '.', $item->identifier );
		foreach( $parts as $nr => $part ){
			if( preg_match( '/[A-Z]/', $part[0] ) )
				$parts[$nr]	= UI_HTML_Tag::create( 'b', $part );
		}
		$identifier	= join( '.', $parts );
		$link	= UI_HTML_Tag::create( 'a', $identifier, array(
			'href'		=> './manage/job/definition/view/'.$item->jobDefinitionId,
			'title'		=> 'Details anzeigen',
		) );
/*		$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit, array(
			'href'		=> './manage/job/definition/edit/'.$item->jobDefinitionId,
			'class'		=> 'btn not-btn-info btn-small',
			'title'		=> 'Eintrag bearbeiten',
		) );*/
/*		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'		=> './manage/job/definition/remove/'.$item->jobDefinitionId,
			'class'		=> 'btn btn-inverse btn-small',
			'title'		=> 'Eintrag entfernen',
		) );*/
/*		$buttonStatus	= UI_HTML_Tag::create( 'a', $iconActivate, array(
			'href'		=> './manage/job/definition/setStatus/'.$item->jobDefinitionId.'/1',
			'class'		=> 'btn btn-success btn-small',
			'title'		=> 'aktivieren',
		) );*/
/*		if( $item->status == Model_Job_Definition::STATUS_ENABLED )
			$buttonStatus	= UI_HTML_Tag::create( 'a', $iconDeactivate, array(
				'href'		=> './manage/job/definition/setStatus/'.$item->jobDefinitionId.'/0',
				'class'		=> 'btn btn-warning btn-small',
				'title'		=> 'deaktivieren',
			 ) );
			 */

/*		$buttons	= UI_HTML_Tag::create( 'div', array(
			$buttonEdit,
			$buttonStatus,
			$buttonRemove
		), array( 'class' => 'btn-group' ) );*/


		$runs	= $item->runs ? '<div>'.$item->runs.' Runs</div>' : '-';
		$fails	= $item->fails ? '<div><span class="text-error">'.$item->fails.' Fails</span></div>' : '';
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link.'<br/><small class="muted">'.$item->className.' >> '.$item->methodName.'</small>', array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $runs.$fails, array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_MODE )->render(), array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_STATUS )->render(), array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $item->lastRunAt ? $helperTime->setTimestamp( $item->lastRunAt )->render() : '-', array( 'class' => '' ) ),
//			UI_HTML_Tag::create( 'td', $buttons, array( 'class' => '' ) ),
		) );
	}
	$cols	= UI_HTML_Elements::ColumnGroup( '', '100px', '120px', '120px', '140px'/*, '140px'*/ );

	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
//		$words['index']['tableHeadId'],
		$words['index']['tableHeadIdentifier'],
		$words['index']['tableHeadStats'],
		$words['index']['tableHeadMode'],
		$words['index']['tableHeadStatus'],
		$words['index']['tableHeadLastRun'],
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', array( $cols, $thead, $tbody ), array( 'class' => 'table table-striped table-condensed' ) );

	/*  --  PAGINATION  --  */
	$pagination	= new \CeusMedia\Bootstrap\PageControl( './manage/job/definition', $page, ceil( $total / $filterLimit ) );
	$table		.= UI_HTML_Tag::create( 'div', $pagination, array( 'class' => 'buttunbar' ) );
}

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $words['index']['heading'] ),
	UI_HTML_Tag::create( 'div', array(
		$table,
		UI_HTML_Tag::create( 'div', array(
			$buttonAdd,
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );

//return print_m( $schedule, NULL, NULL, TRUE );
