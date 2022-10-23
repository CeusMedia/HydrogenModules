<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$optProject	= ['' => '- alle -'];
foreach( $projectMap as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject	= HtmlElements::Options( $optProject, $filterProjectId );

$optStatus	= array_merge( ['' => '- alle -'], $words['states'] );
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

return '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./work/time/archive/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_activity">Aktivität</label>
					<input type="text" name="activity" id="input_activity" class="span12" value="'.htmlentities( $filterQuery, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_projectId">Projekt</label>
					<select name="projectId" id="input_projectId" class="span12">'.$optProject.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">Timer-Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="filter" class="btn btn-small btn-info"><i class="icon-search icon-white"></i> filtern</button>
				<a href="./work/time/filter/reset" class="btn btn-small"><i class="icon-zoom-out"></i> alle</a>
			</div>
		</form>
	</div>
</div>';
