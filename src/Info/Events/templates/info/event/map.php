<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconDetails		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconParticipate	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconNotice			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-star'] );
$iconClose			= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'';

$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-info-circle'] );
$iconView		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] );
$iconMarker		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-map-marker'] );
$iconNote		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-star'] );

$panelSearch	= $view->loadTemplateFile( 'info/event/panel.search.php', ['from' => 'info/event/map'] );

$list	= '<div><em class="muted">...</em></div>';
$map	= '';
if( $center ){
/*	$list	= '<div><em class="muted">Nichts gefunden.</em></div>';
	if( $branches ){
		$list	= [];
		foreach( $branches as $branch ){
			$link	= HtmlTag::create( 'a', $branch->title, array(
				'href'		=> './index/view/'.$branch->branchId,
				'onclick'	=> "clickItem($(this).parent(), true); return false;",
			) );
			$list[]	= HtmlTag::create( 'li', $link, [
				'data-longitude'	=> $branch->longitude,
				'data-latitude'		=> $branch->latitude,
			] );
		}
	}
	$list		= HtmlTag::create( 'ul', $list, ['class' => 'list-branches nav nav-pills nav-stacked'] );
*/
	$list	= '<div><em class="muted">Nichts gefunden.</em></div>';
	if( $events ){
		$list	= [];
		foreach( $events as $nr => $event ){
			$link	= HtmlTag::create( 'a', $iconMarker.'&nbsp;'.$event->title, array(
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
				$logo	= HtmlTag::create( 'img', NULL, [
					'src'		=> 'images/companies/'.$branch->company->logo,
					'width'		=> '64px',
					'height'	=> '64px',
				] );
			}*/
			$heading	= HtmlTag::create( 'div', $link, [
				'class'		=> 'accordion-heading'
			] );
			$buttonView	= HtmlTag::create( 'a', $iconView.'&nbsp;anzeigen', [
				'class'	=> 'btn btn-info',
				'href'	=> './index/view/'.$event->eventId
			] );
			$buttonView		= HtmlTag::create( 'a', $iconView.'&nbsp;anzeigen', [
				'href'			=> './ajax/info/event/modalView/'.$event->eventId,
				'data-toggle'	=> 'modal',
				'data-target'	=> "#modal-event-view",
				'class'			=> 'btn btn-info',
			] );

			$buttonNote	= HtmlTag::create( 'a', $iconNote.'&nbsp;merken', [
				'class'	=> 'btn btn-small',
				'href'	=> './index/note/'.$event->eventId
			] );
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

			$content	= HtmlTag::create( 'div', $info, [
				'class'		=> 'accordion-inner',
			] );
			$body		= HtmlTag::create( 'div', $content, [
				'id'		=> 'accordion-collapse-'.$event->eventId,
				'class'		=> 'accordion-body collapse',
			] );
			$list[]	= HtmlTag::create( 'div', $heading.$body, [
				'class'				=> 'accordion-group',
			] );
		}
	}
	$list		= HtmlTag::create( 'div', $list, [
		'class'		=> 'list-branches accordion',
		'id'		=> 'accordion1',
	] );

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
