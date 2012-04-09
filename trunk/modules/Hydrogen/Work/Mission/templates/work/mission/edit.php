<?php

$w	= (object) $words['edit'];

$priorities		= $words['priorities'];
unset( $priorities[0] );
$optPriority	= UI_HTML_Elements::Options( $priorities, $mission->priority );
$optStatus		= UI_HTML_Elements::Options( $words['states'], $mission->status );

return '
<form action="./work/mission/edit/'.$mission->missionId.'" method="post">
	<fieldset>
		<legend>'.$w->legend.'</legend>
		<ul class="input">
			<li>
				<label for="input_content">'.$w->labelContent.'</label><br/>
				<input type="text" name="content" id="input_content" class="max" value="'.$mission->content.'"/>
			</li>
			<li>
				<label for="input_reference">'.$w->labelReference.'</label><br/>
				<input type="text" name="reference" id="input_reference" class="max" value="'.$mission->reference.'"/>
			</li>
			<li>
				<div class="column-left-20">
					<label for="input_daysLeft">'.$w->labelDaysLeft.'</label><br/>
					<input type="text" name="daysLeft" id="input_daysLeft" class="max" value="'.$mission->daysLeft.'"/>
				</div>
				<div class="column-left-20">
					<label for="input_priority">'.$w->labelPriority.'</label><br/>
					<select name="priority" id="input_priority" class="max">'.$optPriority.'</select>
				</div>
				<div class="column-left-20">
					<label for="input_status">'.$w->labelStatus.'</label><br/>
					<select name="status" id="input_status" class="max">'.$optStatus.'</select>
				</div>
				<div class="column-clear"></div>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './work/mission', $w->buttonCancel, 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'edit', $w->buttonSave, 'button edit' ).'
		</div>
	</fieldset>	
</form>
';

?>