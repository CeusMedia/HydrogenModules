<?php

$iconDetails		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconParticipate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconNotice			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-star' ) );
$iconClose			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'';

$factLabelDate		= date( 'j.n.Y', strtotime( $event->dateStart ) );
$factLabelTime		= date( 'H:i', strtotime( $event->dateStart.' '.$event->timeStart ) ).' &minus; '.date( 'H:i', strtotime( $event->dateStart.' '.$event->timeEnd ) );
$factLabelPlace		= UI_HTML_Tag::create( 'strong', $event->address->title );
$factLabelAddress	= $event->address->street.' '.$event->address->number.'<br/>'.$event->address->postcode.' '.$event->address->city;

return	'
<div>
	<div class="event-title">'.$event->title.'</div>
	<div class="event-description">'.nl2br( $event->description ).'</div>
	<hr/>
	<div class="row-fluid">
		<div class="span7">
			<h4>Ort und Adresse</h4>
			'.$factLabelPlace.'<br/>
			'.$factLabelAddress.'
		</div>
		<div class="span5">
			<h4>Datum und Zeit</h4>
			am '.$factLabelDate.'<br/>
			um '.$factLabelTime.' Uhr
		</div>
	</div>
<!--	<hr/>-->
<!--	'.print_m( $this->getData( 'event' ), NULL, NULL, TRUE ).'-->
<!--	<div class="buttonbar">
		<a href="./info/event/view/'.$event->eventId.'?from=info/event/calender" class="btn btn-info">'.$iconDetails.'&nbsp;weitere Informationen</a>
		<a href="./info/event/setParticipation/'.$event->eventId.'/1?from=info/event/calender" class="btn">'.$iconParticipate.'&nbsp;teilnehmen</a>
		<a href="./info/event/setNotice/'.$event->eventId.'/1?from=info/event/calender" class="btn">'.$iconNotice.'&nbsp;merken</a>
		<button data-dismiss="modal" class="btn btn-inverse" aria-hidden="true">'.$iconClose.'</button>
	</div>-->
</div>
<script>
var modalForm = jQuery("#form-modal-event");
if(modalForm.size()){
	modalForm.find("input[name=\'eventId\']").val('.$event->eventId.');
	modalForm.find("#modal-button-details").bind("click", function(){
		modalForm.find("input[name=\'do\']").val("view");
		modalForm.eq(0).submit();
	});
}
</script>
<style>
.event-title {
	font-size: 2em;
	font-weight: lighter;
	line-height: 2em;
	}
</style>';
