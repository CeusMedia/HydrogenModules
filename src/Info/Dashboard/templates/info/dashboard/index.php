<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var array $dashboards */
/** @var ?object $dashboard */
/** @var ?array $panels */

extract( $view->populateTexts( ['top', 'bottom'], 'html/info/dashboard/' ) );

try{
	$helper		= new View_Helper_Info_Dashboard( $env );
//	print_m( $dashboard );die;
	$helper->setDashboard( $dashboard );
	$helper->setPanels( $panels );

	$formControl	= '';
	if( $moduleConfig->get( 'perUser' ) ){
		$helperAdd	= new View_Helper_Info_Dashboard_Modal_Add( $env );
		$helperAdd->setDashboards( $dashboards );
		$helperAdd->setPanels( $panels );
		$modalAdd	= $helperAdd->render();

		if( empty( $dashboard ) ){
			$content	= $view->loadContentFile( 'html/info/dashboard/empty.html', ['user' => $user] );
			return $textTop.$content.$textBottom.$modalAdd;
		}

		$iconAddBoard	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-th'] );
		$iconAddPanel	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-square'] );
		$iconEditTitle	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
		$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

		$countUserBoards	= count( $dashboards );
		$countUserPanels	= count( explode( ',', $dashboard->panels ) );
		$maxUserBoards		= $moduleConfig->get( 'perUser.maxBoards' );
		$maxUserPanels		= $moduleConfig->get( 'perUser.maxPanels' );
		$buttonAddBoard		= HtmlTag::create( 'button', $iconAddBoard, array(
			'type'		=> 'button',
			'class'		=> 'btn not-btn-small btn-success trigger-myModalInfoDashboardAdd',
			'title'		=> $countUserBoards >= $maxUserBoards ? 'Maximum erreicht' : 'neues Dashboard',
			'disabled'	=> $countUserBoards >= $maxUserBoards ? 'disabled' : NULL,
		) );
		$buttonAddPanel		= HtmlTag::create( 'button', $iconAddPanel, array(
			'type'		=> 'button',
			'class'		=> 'btn not-btn-small btn-success trigger-myModalInfoDashboardAddPanel',
			'title'		=> $countUserPanels >= $maxUserPanels ? 'Maximum erreicht' : 'neues Panel im Dashboard',
			'disabled'	=> $countUserPanels >= $maxUserPanels ? 'disabled' : NULL,
		) );
		$buttonEditTitle	= HtmlTag::create( 'button', $iconEditTitle, array(
			'type'				=> 'button',
			'class'				=> 'btn btn-small button-rename-board',
			'data-dashboard-id'	=> $dashboard->dashboardId,
			'data-title'		=> htmlentities( $dashboard->title, ENT_QUOTES, 'UTF-8' ),
			) );
		$buttonRemoveBoard	= HtmlTag::create( 'a', $iconRemove, array(
			'href'		=> './info/dashboard/remove/'.$dashboard->dashboardId,
			'class'		=> 'btn btn-small btn-inverse',
			'title'		=> 'aktuelles Dashboard verwerfen',
			'onclick'	=> 'if(!confirm(\'Wirklich das aktuelle Dashboard auflösen?\'))return false;',
		) );

		$optDashboardId	= [];
		foreach( $dashboards as $entry )
			$optDashboardId[$entry->dashboardId]	= $entry->title;
		$optDashboardId	= HtmlElements::Options( $optDashboardId, $dashboard->dashboardId );

		$helperAddPanel	= new View_Helper_Info_Dashboard_Modal_AddPanel( $env );
		$helperAddPanel->setDashboard( $dashboard );
		$helperAddPanel->setPanels( $panels );
		$modalAddPanel	= $helperAddPanel->render();

		$formControl = '
			<form class="form-inline">
				<select name="dashboardId" id="input_dashboardId">'.$optDashboardId.'</select>
				<div class="btn-group">
					'.$buttonEditTitle.'
					'.$buttonRemoveBoard.'
				</div>
				<div class="btn-group">
					'.$buttonAddBoard.'
					'.$buttonAddPanel.'&nbsp;
				</div>
			</form>';

		return $textTop.'
		<div class="row-fluid">
			<div class="span6">
				<h3 class="autocut"><span class="muted">Dashboard: </span> '.$dashboard->title.'</h3>
			</div>
			<div class="span6 visible-desktop">
				<div class="pull-right">
					'.$formControl.'
				</div>
			</div>
		</div>
		'.$helper->render().'
		<div class="row-fluid hidden-desktop">
			<div class="span12">
				'.$formControl.'
			</div>
		</div>
		'.$textBottom.$modalAdd.$modalAddPanel;
	}
	$heading	= HtmlTag::create( 'h3', 'Dashboard' );
	return $textTop.$heading.$helper->render().$textBottom;
}
catch( Exception $e ){
	die( $e->getMessage() );
}
