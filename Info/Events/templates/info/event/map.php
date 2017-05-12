<?php

$iconDetails		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconParticipate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconNotice			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-star' ) );
$iconClose			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'';

$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-info-circle' ) );
$iconView		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconMarker		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-map-marker' ) );
$iconNote		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-star' ) );

$panelSearch	= $view->loadTemplateFile( 'info/event/panel.search.php', array( 'from' => 'info/event/map' ) );

$list	= '<div><em class="muted">...</em></div>';
$map	= '';
if( $center ){
/*	$list	= '<div><em class="muted">Nichts gefunden.</em></div>';
	if( $branches ){
		$list	= array();
		foreach( $branches as $branch ){
			$link	= UI_HTML_Tag::create( 'a', $branch->title, array(
				'href'		=> './index/view/'.$branch->branchId,
				'onclick'	=> "clickItem($(this).parent(), true); return false;",
			) );
			$list[]	= UI_HTML_Tag::create( 'li', $link, array(
				'data-longitude'	=> $branch->longitude,
				'data-latitude'		=> $branch->latitude,
			) );
		}
	}
	$list		= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'list-branches nav nav-pills nav-stacked' ) );
*/
	$list	= '<div><em class="muted">Nichts gefunden.</em></div>';
	if( $events ){
		$list	= array();
		foreach( $events as $nr => $event ){
			$link	= UI_HTML_Tag::create( 'a', $iconMarker.'&nbsp;'.$event->title, array(
			//	'href'				=> './index/view/'.$branch->branchId,
				'href'				=> '#accordion-collapse-'.$event->eventId,
				'onclick'			=> "clickItem($(this), true);",
				'class'				=> 'accordion-toggle autocut map-point collapsed',
				'data-toggle'		=> 'collapse',
				'data-parent'		=> '#accordion1',
				'data-longitude'	=> $event->longitude,
				'data-latitude'		=> $event->latitude,
				'data-marker-title' => htmlentities( $event->title, ENT_QUOTES, 'UTF-8' )
			) );
			$logo	= '';
/*			if( $branch->company->logo ){
				$logo	= UI_HTML_Tag::create( 'img', NULL, array(
					'src'		=> 'images/companies/'.$branch->company->logo,
					'width'		=> '64px',
					'height'	=> '64px',
				) );
			}*/
			$heading	= UI_HTML_Tag::create( 'div', $link, array(
				'class'		=> 'accordion-heading'
			) );
			$buttonView	= UI_HTML_Tag::create( 'a', $iconView.'&nbsp;anzeigen', array(
				'class'	=> 'btn btn-info',
				'href'	=> './index/view/'.$event->eventId
			) );
			$buttonView		= UI_HTML_Tag::create( 'a', $iconView.'&nbsp;anzeigen', array(
				'href'			=> './info/event/modalView/'.$event->eventId,
				'data-toggle'	=> 'modal',
				'data-target'	=> "#modal-event-view",
				'class'			=> 'btn btn-info',
			) );

			$buttonNote	= UI_HTML_Tag::create( 'a', $iconNote.'&nbsp;merken', array(
				'class'	=> 'btn btn-small',
				'href'	=> './index/note/'.$event->eventId
			) );
			$info	= '
<div class="row-fluid">
	<div class="" style="width: 80px; float: left">
		'.$logo.'
	</div>
	<div class="" style="width: 100px; float: right">
		'.$buttonView.'
		'.$buttonNote.'
	</div>
	<div class="" style="float: left">
		<big><strong>Adresse</strong></big>
		<div class="vcard">
			'.$event->street.' '.$event->number.'<br/>
			'.$event->postcode.' '.$event->city.'<br/>
		</div>
	</div>
</div>';

			$content	= UI_HTML_Tag::create( 'div', $info, array(
				'class'		=> 'accordion-inner',
			) );
			$body		= UI_HTML_Tag::create( 'div', $content, array(
				'id'		=> 'accordion-collapse-'.$event->eventId,
				'class'		=> 'accordion-body collapse',
			) );
			$list[]	= UI_HTML_Tag::create( 'div', $heading.$body, array(
				'class'				=> 'accordion-group',
			) );
		}
	}
	$list		= UI_HTML_Tag::create( 'div', $list, array(
		'class'		=> 'list-branches accordion',
		'id'		=> 'accordion1',
	) );

	$helperMap	= new View_Helper_Map( $env );
	$map		= $helperMap->render( $center->lat, $center->lon, NULL, NULL, 13 );
}

$panelList	= '
<div class="not-content-panel">
<!--	<h3>Results</h3>-->
	<div class="not-content-panel-inner">
		'.$list.'
	</div>
</div>';

$layout	= '
';

return '
<div class="row-fluid">
	<div class="span12">
		'.$panelSearch.'
	</div>
</div>
<hr/>
<div class="row-fluid">
	<div class="span6">
		'.$panelList.'
	</div>
	<div class="span6">
		'.$map.'
	</div>
</div>
<form id="form-modal-event" action="./info/event/modal" method="post">
	<input type="hidden" name="eventId" value="0"/>
	<input type="hidden" name="do" value=""/>
	<div id="modal-event-view" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">Wichtigste Informationen</h3>
		</div>
		<div class="modal-body"></div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary" id="modal-button-details">'.$iconDetails.'&nbsp;weitere Informationen</button>
			<button data-dismiss="modal" class="btn btn-inverse" aria-hidden="true">'.$iconClose.'</button>
		</div>
	</div>
</form>
<style>
.UI_Map {
	height: 400px;
	}
</style>
<script>jQuery(document).ready(function(){});</script>
';
