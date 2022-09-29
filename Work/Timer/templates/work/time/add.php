<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$w			= (object) $words['add'];

$optStatus	= $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $defaultStatus );

$optProject	= [];
foreach( $projectMap as $projectId => $project )
	$optProject[$projectId]	= $project->title;
$optProject	= HtmlElements::Options( $optProject, $defaultProjectId );

extract( $view->populateTexts( ['add.top', 'add.bottom', 'add.info'], 'html/work/time/' ) );

//$panelFilter	= $view->loadTemplateFile( 'work/time/index.filter.php' );

return $textAddTop.'
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel content-panel-form">
			<h3>Zeit erfassen</h3>
			<div class="content-panel-inner">
				<form action="./work/time/add" method="post">
					<input type="hidden" name="from" value="'.$from.'"/>
					<input type="hidden" name="workerId" value="'.$userId.'"/>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_projectId">'.$w->labelProjectId.'</label>
							<select name="projectId" id="input_projectId" class="span12">'.$optProject.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" required="required"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_time_planned">'.$w->labelTimePlanned.'</label>
							<input type="text" name="time_planned" id="input_time_planned" class="span12" value="0h 00m" required="required"/>
						</div>
						<div class="span4">
							<label for="input_time_needed">'.$w->labelTimeNeeded.'</label>
							<input type="text" name="time_needed" id="input_time_needed" class="span12" value="0h 00m"/>
						</div>
						<div class="span4">
							<label for="input_status">'.$w->labelStatus.'</label>
							<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_description">'.$w->labelDescription.'</label>
							<textarea name="description" id="input_description" rows="5" class="span12"></textarea>
						</div>
					</div>
					<div class="buttonbar">
						<a href="./work/time" class="btn btn-small"><i class="fa fa-fw fa-arrow-left"></i> '.$w->buttonCancel.'</a>
						<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> '.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span6">
		'.$textAddInfo.'
	</div>
</div>
'.$textAddBottom;
