<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var array $projectMap */
/** @var object $timer */

$w			= (object) $words['edit'];

$optStatus	= $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $timer->status );

$optProject	= [];
foreach( $projectMap as $projectId => $project )
	$optProject[$projectId]	= $project->title;
$optProject	= HtmlElements::Options( $optProject, $timer->projectId );

extract( $view->populateTexts( ['edit.top', 'edit.bottom', 'edit.info'], 'html/work/time/' ) );

$timePlanned	= View_Helper_Work_Time::formatSeconds( $timer->secondsPlanned );
$timeNeeded		= View_Helper_Work_Time::formatSeconds( $timer->secondsNeeded );

$optWorker	= [];
foreach( $projectUsers as $projectUser )
	$optWorker[$projectUser->userId]	= $projectUser->username;
$optWorker	= HtmlElements::Options( $optWorker, $timer->workerId );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
	'href'	=> './'.( $from ?: 'work/time' ),
	'class'	=> "btn btn-small"
] );

$buttonSave	= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
	'type'		=> "submit",
	'name'		=> "save",
	'class'		=> "btn btn-primary"
] );

$panelRelated	= $view->loadTemplateFile( 'work/time/edit.related.php' );

return $textEditTop.'
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel content-panel-form">
			<h3>Vorgang bearbeiten</h3>
			<div class="content-panel-inner">
				<form action="./work/time/edit/'.$timer->workTimerId.'" method="post">
					<input type="hidden" name="from" value="'.htmlentities( $from, ENT_QUOTES, 'UTF-8' ).'">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">'.$w->labelTitle.'</label>
							'.HtmlTag::create( 'input', NULL, [
								'type'		=> 'text',
								'name'		=> 'title',
								'id'		=> 'input_title',
								'class'		=> 'span12',
								'value'		=> htmlentities( $timer->title, ENT_QUOTES, 'UTF-8' ),
							] ).'
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_workerId">'.$w->labelWorkerId.'</label>
							'.HtmlTag::create( 'select', $optWorker, [
								'name'		=> 'workerId',
								'id'		=> 'input_workerId',
								'class'		=> 'span12',
							] ).'
						</div>
						<div class="span8">
							<label for="input_projectId">'.$w->labelProjectId.'</label>
							'.HtmlTag::create( 'select', $optProject, [
								'name'		=> 'projectId',
								'id'		=> 'input_projectId',
								'class'		=> 'span12',
								'readonly'	=> TRUE,
								'disabled'	=> TRUE,
							] ).'
						</div>
					</div>
					<div class="row-fluid">
						<div class="span4">
							<label for="input_time_planned">'.$w->labelTimePlanned.'</label>
							'.HtmlTag::create( 'input', NULL, [
								'type'		=> 'text',
								'name'		=> 'time_planned',
								'id'		=> 'input_time_planned',
								'class'		=> 'span12',
								'value'		=> $timePlanned,
							] ).'
						</div>
						<div class="span4">
							<label for="input_time_needed">'.$w->labelTimeNeeded.'</label>
							'.HtmlTag::create( 'input', NULL, [
								'type'		=> 'text',
								'name'		=> 'time_needed',
								'id'		=> 'input_time_needed',
								'class'		=> 'span12',
								'value'		=> $timer->status == 1 ? 'wird erfasst' : $timeNeeded,
								'readonly'	=> $timer->status == 1 ? 'readonly' : NULL,
								'disabled'	=> $timer->status == 1 ? 'disabled' : NULL,
							] ).'
						</div>
						<div class="span4">
							<label for="input_status">'.$w->labelStatus.'</label>
							'.HtmlTag::create( 'select', $optStatus, [
								'name'		=> 'status',
								'id'		=> 'input_status',
								'class'		=> 'span12',
							] ).'
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_description">'.$w->labelDescription.'</label>
							<textarea name="description" id="input_description" rows="10" class="span12">'.htmlentities( $timer->description, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span6">
		'.$textEditInfo.'
		'.$panelRelated.'
	</div>
</div>
'.$textEditBottom;
