<?php

$optMode	= $words['index-filter-modes'];
$optMode	= UI_HTML_Elements::Options( $words['index-filter-modes'], $filterMode );

$optDuration	= $words['index-filter-durations'];
$optDuration	= UI_HTML_Elements::Options( $words['index-filter-durations'], $filterDuration );

$optYear	= array();
$year		= (int) date( "Y" );
$yearMin	= $year - 4;
for( $i=$year; $i>$yearMin; $i-- )
	$optYear[$i]	= $i;
$optYear	= UI_HTML_Elements::Options( $optYear, $filterYear );

$optMonth	= $words['index-filter-months'];
$optMonth	= UI_HTML_Elements::Options( $words['months'], $filterMonth );

$optProjectIds	= array();
foreach( $allProjects as $project )
	$optProjectIds[$project->projectId]	= $project->title;
$optProjectIds	= UI_HTML_Elements::Options( $optProjectIds, $filterProjectIds );

$optUserIds	= array();
foreach( $allUsers as $user )
	$optUserIds[$user->userId]	= $user->username;
$optUserIds	= UI_HTML_Elements::Options( $optUserIds, $filterUserIds );


//$lastWeek	= (int) date( "W", min( strtotime( $filterYear.'-12-31' ), time() ) );
$lastWeek	= (int) date( "W", min( strtotime( '2016-12-31' ), time() ) );
$optWeek	= array();
for( $i=$lastWeek; $i>0; $i-- )
	$optWeek[$i]	= "KW ".$i;
$optWeek	= UI_HTML_Elements::Options( $optWeek, $filterWeek );

return '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./work/time/analysis/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_mode">Sichtweise</label>
					<select name="mode" id="input_mode" class="span12 has-optionals">'.$optMode.'</select>
				</div>
			</div>
			<div class="row-fluid optional mode mode-projects" '.( $filterMode !== 'projects' ? 'style="display: none"' : '' ).'>
				<div class="span12">
					<label for="input_projectId">Projekte</label>
					<select name="projectIds[]" id="input_projectIds" class="span12" multiple="multiple" size="'.( min( 15, max( 3, count( $allProjects ) ) ) + 1 ).'">'.$optProjectIds.'</select>
				</div>
			</div>
			<div class="row-fluid optional mode mode-users" '.( $filterMode !== 'users' ? 'style="display: none"' : '' ).'>
				<div class="span12">
					<label for="input_userIds">Bearbeiter</label>
					<select name="userIds[]" id="input_userIds" class="span12" multiple="multiple" size="'.( min( 15, max( 3, count( $allUsers ) ) ) + 1 ).'">'.$optUserIds.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_duration">Zeitraum</label>
					<select name="duration" id="input_duration" class="span12 has-optionals">'.$optDuration.'</select>
				</div>
			</div>
			<div class="row-fluid optional duration duration-duration" style="display: none">
				<div class="span6">
					<label for="input_durationFrom">Von</label>
					<input type="text" name="durationFrom" id="input_durationFrom" class="span12" class="span12" value="'.htmlentities( $filterDurationFrom ).'"/>
				</div>
				<div class="span6">
					<label for="input_durationTo">Bis</label>
					<input type="text" name="durationTo" id="input_durationTo" class="span12" class="span12" value="'.htmlentities( $filterDurationTo ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6 optional duration duration-year duration-month duration-week" style="display: none">
					<label for="input_year">Jahr</label>
					<select name="year" id="input_year" class="span12" class="span12">'.$optYear.'</select>
				</div>
				<div class="span6 optional duration duration-month" style="display: none">
					<label for="input_month">Monat</label>
					<select name="month" id="input_month" class="span12" class="span12">'.$optMonth.'</select>
				</div>
				<div class="span6 optional duration duration-week" style="display: none">
					<label for="input_week">Kalenderwoche</label>
					<select name="week" id="input_week" class="span12" class="span12">'.$optWeek.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					<button type="submit" name="filter" class="btn btn-small btn-info"><i class="fa fa-fw fa-search"></i>&nbsp;filter</button>
					<a href="./work/time/analysis/filter/reset" class="btn btn-small btn-inverse"><i class="fa fa-fw fa-search-minus"></i>&nbsp;alle</a>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
jQuery(document).ready(function(){
	var dateInputs = $("#input_durationFrom, #input_durationTo" );
	dateInputs.datepicker({
		dateFormat: "yy-mm-dd",
	//	appendText: "(yyyy-mm-dd)",
	//	buttonImage: "/images/datepicker.gif",
	//	changeMonth: true,
	//	changeYear: true,
	//	gotoCurrent: true,
	//	autoSize: true,
		firstDay: 1,
		nextText: "nächster Monat",
		prevText: "vorheriger Monat",
		yearRange: "c:c+4",
		monthNames: monthNames
	});
});
</script>';
