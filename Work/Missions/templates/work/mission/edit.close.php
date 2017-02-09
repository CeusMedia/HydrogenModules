<?php

$w	= (object) $words['edit-panel-close'];

if( $mission->status < 0 )
	return '';

$hint	= '';

$minutes	= $mission->minutesRequired;
if( $useTimer ){
	$seconds	= View_Helper_Work_Time::sumTimersOfModuleId( $env, 'Work_Missions', $mission->missionId );
	if( $seconds )
		$minutes	= ceil( $seconds / 60 );

	if( $openTimers ){
		$hint		= UI_HTML_Tag::create( 'div', 'Bitte vorher die Aktivitäten beenden!', array( 'class' => 'alert alert-danger' ) );
	}
}

$hoursRequired		= floor( $minutes / 60 );
$minutesRequired	= str_pad( $minutes - $hoursRequired * 60, 2, 0, STR_PAD_LEFT );

$timeRequired		= View_Work_Mission::formatSeconds( $minutes * 60 );

return '
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/mission/close/'.$mission->missionId.'" method="post" id="form-mission-close">
			<input type="hidden" name="status" id="input_close_status" value="'.$mission->status.'"/>
			'.$hint.'
			<div class="row-fluid">
<!--				<div class="span7">
					<label for="input_close_hoursRequired">'.$w->labelHours.'</label>
				</div>
				<div class="span5">
					<input type="text" name="hoursRequired" id="input_close_hoursRequired" class="span8 -xs numeric" value="'.$hoursRequired.':'.$minutesRequired.'"/>
				</div>-->
				<div class="span7">
					<label for="input_close_timeRequired">'.$w->labelTimeRequired.'</label>
				</div>
				<div class="span5">
					<input type="text" name="timeRequired" id="input_close_timeRequired" class="span8 -xs numeric" value="'.$timeRequired.'"/>
				</div>
			</div>
			<div class="buttonbar">
				<div class="not-btn-group">
					<button type="button" onclick="closeMission(4, \''.$w->buttonCloseConfirm.'\');" class="btn btn-small btn-success">
						<i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonClose.'
					</button>
					<button type="button" onclick="closeMission(-2, \''.$w->buttonCancelConfirm.'\');" class="btn btn-small btn-danger">
						<i class="icon-remove icon-white"></i>&nbsp;'.$w->buttonCancel.'
					</button>
					<button type="button" onclick="closeMission(-3, \''.$w->buttonRemoveConfirm.'\');" class="btn btn-small btn-inverse">
						<i class="icon-trash icon-white"></i>&nbsp;'.$w->buttonRemove.'
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
function closeMission(status, confirm){
	if(window.confirm(confirm)){
		$("#input_close_status").val(status).closest("form").submit();
	}
}
</script>';
?>
