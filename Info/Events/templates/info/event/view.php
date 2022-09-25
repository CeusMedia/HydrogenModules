<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconBack		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );

$facts	= [];
$facts[]	= HtmlTag::create( 'dt', 'Datum' );
$facts[]	= HtmlTag::create( 'dd', date( 'j.n.Y', strtotime( $event->dateStart ) ) );
$facts[]	= HtmlTag::create( 'dt', 'Zeit' );
$facts[]	= HtmlTag::create( 'dd', date( 'H:i', strtotime( $event->dateStart.' '.$event->timeStart ) ).' &minus; '.date( 'H:i', strtotime( $event->dateStart.' '.$event->timeEnd ) ).' Uhr' );

$facts[]	= HtmlTag::create( 'dt', 'Ort' );
$facts[]	= HtmlTag::create( 'dd', $event->address->title );
$facts[]	= HtmlTag::create( 'dt', 'Adresse' );
$facts[]	= HtmlTag::create( 'dd', $event->address->street.' '.$event->address->number.'<br/>'.$event->address->postcode.' '.$event->address->city );

$facts		= HtmlTag::create( 'dl', $facts, array( 'class' => 'not-dl-horizontal' ) );

$urlBack	= $from ? './'.$from : './event';

return	'
<a href="'.$urlBack.'" class="btn btn-small">'.$iconBack.'&nbsp;zurück</a>
<div>
	<div class="event-title">'.$event->title.'</div>
	<div class="event-description">'.nl2br( $event->description ).'</div>
	<!--<h4>Wann und wo?</h4>-->
	<div class="row-fluid">
		<div class="span4">
			<div class="event-facts">
				'.$facts.'
			</div>
		</div>
		<div class="span8">
			<div id="map-address-'.$event->eventId.'" style="height: 400px" data-latitude="'.$event->address->latitude.'" data-longitude="'.$event->address->longitude.'"></div>
		</div>
	</div>
<!--	'.print_m( $this->getData( 'event' ), NULL, NULL, TRUE ).'-->
	<div class="buttonbar">
	</div>
</div>
<script>
jQuery(document).ready(function(){
	var map = Module_UI_Map.loadMap("map-address-'.$event->eventId.'");
	Module_UI_Map.addMarker(map, '.$event->address->latitude.', '.$event->address->longitude.');
});
</script>
<style>
.event-title {
	font-size: 2em;
	font-weight: lighter;
	line-height: 2em;
	}
</style>';
