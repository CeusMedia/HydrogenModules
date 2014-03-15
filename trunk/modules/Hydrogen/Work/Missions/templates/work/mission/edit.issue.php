<?php

if( !$useIssues )
	return '';
return '
<div class="content-panel content-panel-form">
	<h3>Zum Problem machen</h3>
	<div class="content-panel-inner">
		<form action="./work/mission/convert/'.$mission->missionId.'/issue" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Titel des Problems</label>
					<input type="text" name="title" id="input_title" class="span12 -max" value="'.htmlentities( $mission->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_issue_type">Typ</label>
					<select type="text" name="type" id="input_issue_type" class="span12 -max"></select>
				</div>
				<div class="span6">
					<label for="input_issue_status">Status</label>
					<select type="text" name="status" id="input_issue_status" class="span12 -max"></select>
				</div>
			</div>
		</form>
	</div>
</div>';
?>
