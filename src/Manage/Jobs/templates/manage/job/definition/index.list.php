<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$helperAttribute	= new View_Helper_Job_Attribute( $env );

$statusLabels	= $wordsGeneral['job-definition-statuses'];
//print_m($definitions);die;

$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconActivate	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-toggle-on'] );
$iconDeactivate	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-toggle-off'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$buttonAdd		= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], array(
	'href'	=> './manage/job/definition/add',
	'class'	=> 'btn btn-success',
) );

$table	= HtmlTag::create( 'div', 'Noch keine Jobs geplant.', ['class' => 'alert'] );

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
				$parts[$nr]	= HtmlTag::create( 'b', $part );
		}
		$identifier	= join( '.', $parts );
		$link	= HtmlTag::create( 'a', $identifier, array(
			'href'		=> './manage/job/definition/view/'.$item->jobDefinitionId,
			'title'		=> 'Details anzeigen',
		) );
/*		$buttonEdit		= HtmlTag::create( 'a', $iconEdit, array(
			'href'		=> './manage/job/definition/edit/'.$item->jobDefinitionId,
			'class'		=> 'btn not-btn-info btn-small',
			'title'		=> 'Eintrag bearbeiten',
		) );*/
/*		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, array(
			'href'		=> './manage/job/definition/remove/'.$item->jobDefinitionId,
			'class'		=> 'btn btn-inverse btn-small',
			'title'		=> 'Eintrag entfernen',
		) );*/
/*		$buttonStatus	= HtmlTag::create( 'a', $iconActivate, array(
			'href'		=> './manage/job/definition/setStatus/'.$item->jobDefinitionId.'/1',
			'class'		=> 'btn btn-success btn-small',
			'title'		=> 'aktivieren',
		) );*/
/*		if( $item->status == Model_Job_Definition::STATUS_ENABLED )
			$buttonStatus	= HtmlTag::create( 'a', $iconDeactivate, array(
				'href'		=> './manage/job/definition/setStatus/'.$item->jobDefinitionId.'/0',
				'class'		=> 'btn btn-warning btn-small',
				'title'		=> 'deaktivieren',
			 ) );
			 */

/*		$buttons	= HtmlTag::create( 'div', array(
			$buttonEdit,
			$buttonStatus,
			$buttonRemove
		), ['class' => 'btn-group'] );*/


		$runs	= $item->runs ? '<div>'.$item->runs.' Runs</div>' : '-';
		$fails	= $item->fails ? '<div><span class="text-error">'.$item->fails.' Fails</span></div>' : '';
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link.'<br/><small class="muted">'.$item->className.' >> '.$item->methodName.'</small>', ['class' => ''] ),
			HtmlTag::create( 'td', $runs.$fails, ['class' => ''] ),
			HtmlTag::create( 'td', $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_MODE )->render(), ['class' => ''] ),
			HtmlTag::create( 'td', $helperAttribute->setAttribute( View_Helper_Job_Attribute::ATTRIBUTE_DEFINITION_STATUS )->render(), ['class' => ''] ),
			HtmlTag::create( 'td', $item->lastRunAt ? $helperTime->setTimestamp( $item->lastRunAt )->render() : '-', ['class' => ''] ),
//			HtmlTag::create( 'td', $buttons, ['class' => ''] ),
		) );
	}
	$cols	= HtmlElements::ColumnGroup( '', '100px', '120px', '120px', '140px'/*, '140px'*/ );

	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
//		$words['index']['tableHeadId'],
		$words['index']['tableHeadIdentifier'],
		$words['index']['tableHeadStats'],
		$words['index']['tableHeadMode'],
		$words['index']['tableHeadStatus'],
		$words['index']['tableHeadLastRun'],
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$cols, $thead, $tbody], ['class' => 'table table-striped table-condensed'] );

	/*  --  PAGINATION  --  */
	$pagination	= new \CeusMedia\Bootstrap\PageControl( './manage/job/definition', $page, ceil( $total / $filterLimit ) );
	$table		.= HtmlTag::create( 'div', $pagination, ['class' => 'buttunbar'] );
}

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $words['index']['heading'] ),
	HtmlTag::create( 'div', array(
		$table,
		HtmlTag::create( 'div', array(
			$buttonAdd,
		), ['class' => 'buttonbar'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

//return print_m( $schedule, NULL, NULL, TRUE );
