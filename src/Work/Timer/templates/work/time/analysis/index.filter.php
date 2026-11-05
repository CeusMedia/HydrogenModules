<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var array<object> $allProjects */
/** @var array<object> $allUsers */

$w	= (object) $words['index-filter'];

$optMode	= $words['index-filter-modes'];
$optMode	= HtmlElements::Options( $words['index-filter-modes'], $filterMode );

$optDuration	= $words['index-filter-durations'];
$optDuration	= HtmlElements::Options( $words['index-filter-durations'], $filterDuration );

$optYear	= [];
$year		= (int) date( "Y" );
$yearMin	= $year - 4;
for( $i=$year; $i>$yearMin; $i-- )
	$optYear[$i]	= $i;
$optYear	= HtmlElements::Options( $optYear, $filterYear );

$optMonth	= $words['index-filter-months'];
$optMonth	= HtmlElements::Options( $words['months'], $filterMonth );

$optProjectIds	= [];
foreach( $allProjects as $project )
	$optProjectIds[$project->projectId]	= $project->title;
$optProjectIds	= HtmlElements::Options( $optProjectIds, $filterProjectIds );

$optUserIds	= [];
foreach( $allUsers as $user )
	$optUserIds[$user->userId]	= $user->username;
$optUserIds	= HtmlElements::Options( $optUserIds, $filterUserIds );


//$lastWeek	= (int) date( "W", min( strtotime( $filterYear.'-12-31' ), time() ) );
$lastWeek	= (int) date( "W", min( strtotime( '2016-12-31' ), time() ) );
$optWeek	= [];
for( $i=$lastWeek; $i>0; $i-- )
	$optWeek[$i]	= sprintf( $w->optionWeek, $i );
$optWeek	= HtmlElements::Options( $optWeek, $filterWeek );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./work/time/analysis/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_mode">'.$w->labelMode.'</label>
					<select name="mode" id="input_mode" class="span12 has-optionals">'.$optMode.'</select>
				</div>
			</div>
			<div class="row-fluid optional mode mode-projects" '.( $filterMode !== 'projects' ? 'style="display: none"' : '' ).'>
				<div class="span12">
					<label for="input_projectId">'.$w->labelProjectIds.'</label>
					<select name="projectIds[]" id="input_projectIds" class="span12" multiple="multiple" size="'.( min( 15, max( 3, count( $allProjects ) ) ) + 1 ).'">'.$optProjectIds.'</select>
				</div>
			</div>
			<div class="row-fluid optional mode mode-users" '.( $filterMode !== 'users' ? 'style="display: none"' : '' ).'>
				<div class="span12">
					<label for="input_userIds">'.$w->labelUserIds.'</label>
					<select name="userIds[]" id="input_userIds" class="span12" multiple="multiple" size="'.( min( 15, max( 3, count( $allUsers ) ) ) + 1 ).'">'.$optUserIds.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_duration">'.$w->labelDuration.'</label>
					<select name="duration" id="input_duration" class="span12 has-optionals">'.$optDuration.'</select>
				</div>
			</div>
			<div class="row-fluid optional duration duration-duration" style="display: none">
				<div class="span6">
					<label for="input_durationFrom">'.$w->labelDurationFrom.'</label>
					<input type="text" name="durationFrom" id="input_durationFrom" class="span12" class="span12" value="'.htmlentities( $filterDurationFrom ).'"/>
				</div>
				<div class="span6">
					<label for="input_durationTo">'.$w->labelDurationTo.'</label>
					<input type="text" name="durationTo" id="input_durationTo" class="span12" class="span12" value="'.htmlentities( $filterDurationTo ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6 optional duration duration-year duration-month duration-week" style="display: none">
					<label for="input_year">'.$w->labelYear.'</label>
					<select name="year" id="input_year" class="span12" class="span12">'.$optYear.'</select>
				</div>
				<div class="span6 optional duration duration-month" style="display: none">
					<label for="input_month">'.$w->labelMonth.'</label>
					<select name="month" id="input_month" class="span12" class="span12">'.$optMonth.'</select>
				</div>
				<div class="span6 optional duration duration-week" style="display: none">
					<label for="input_week">'.$w->labelWeek.'</label>
					<select name="week" id="input_week" class="span12" class="span12">'.$optWeek.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					<button type="submit" name="filter" class="btn btn-small btn-info"><i class="fa fa-fw fa-search"></i>&nbsp;'.$w->buttonFilter.'</button>
					<a href="./work/time/analysis/filter/reset" class="btn btn-small btn-inverse"><i class="fa fa-fw fa-search-minus"></i>&nbsp;'.$w->buttonReset.'</a>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
jQuery(document).ready(function(){
	let dateInputs = $("#input_durationFrom, #input_durationTo" );
	dateInputs.datepicker({
		dateFormat: "yy-mm-dd",
	//	appendText: "(yyyy-mm-dd)",
	//	buttonImage: "/images/datepicker.gif",
	//	changeMonth: true,
	//	changeYear: true,
	//	gotoCurrent: true,
	//	autoSize: true,
		firstDay: 1,
		nextText: '.json_encode( $w->datepickerNext ).',
		prevText: '.json_encode( $w->datepickerPrev ).',
		yearRange: "c:c+4",
		monthNames: monthNames
	});
});
</script>';
