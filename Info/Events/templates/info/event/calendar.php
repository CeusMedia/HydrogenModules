<?php

$iconDetails		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
$iconParticipate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconNotice			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-star' ) );
$iconClose			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'';

$helper	= new View_Helper_Info_Event_Calendar( $this->env );
$helper->setEvents( $this->getData( 'events' ) );
$helper->setMonth( $this->getData( 'year' ), $this->getData( 'month' ) );

return $helper->render().'
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
</form>';
?>
