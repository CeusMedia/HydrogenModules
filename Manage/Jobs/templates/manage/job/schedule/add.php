<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;'.$words['add']['buttonCancel'], array(
	'href'	=> './manage/job/schedule',
	'class' => 'btn',
) );
$buttonAdd		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;'.$words['add']['buttonAdd'], array(
	'class' => 'btn btn-primary',
	'type'	=> 'submit',
	'name'	=> 'save',
) );

$optStatus	= $wordsGeneral['job-definition-statuses'];
$optStatus	= UI_HTML_Elements::Options( $optStatus );


$optDefinition	= [];
foreach( $definitionMap as $definitionId => $definition )
	$optDefinition[$definitionId]	= $definition->identifier;
$optDefinition	= UI_HTML_Elements::Options( $optDefinition );


$optFormat			= array(
	'cron-month'	=> 'Cron: Monatstage',
	'cron-week'		=> 'Cron: Wochentage',
	'interval'		=> 'Intervall',
	'datetime'		=> 'Datum (einmalig)'
);
$optFormat			= UI_HTML_Elements::Options( $optFormat );

$optMinuteOfHour	= array_merge( $words['options-minuteOfHour'], array( 'value' => 'genau:', 'range' => 'Bereich:', 'values' => 'mehrere:' ) );
$optMinuteOfHour	= UI_HTML_Elements::Options( $optMinuteOfHour );

$optHourOfDay		= array_merge( $words['options-hourOfDay'], array( 'value' => 'genau:', 'range' => 'Bereich:', 'values' => 'mehrere:' ) );
$optHourOfDay		= UI_HTML_Elements::Options( $optHourOfDay );

$optDayOfWeek		= array_merge( $words['options-dayOfWeek'], array( 'value' => 'genau:', 'range' => 'Bereich:', 'values' => 'mehrere:' ) );
$optDayOfWeek		= UI_HTML_Elements::Options( $optDayOfWeek );

$optDayOfMonth		= array_merge( $words['options-dayOfMonth'], array( 'value' => 'genau:', 'range' => 'Bereich:', 'values' => 'mehrere:' ) );
$optDayOfMonth		= UI_HTML_Elements::Options( $optDayOfMonth );

$optMonthOfYear		= array_merge( $words['options-monthOfYear'], array( 'value' => 'genau:', 'range' => 'Bereich:', 'values' => 'mehrere:' ) );
$optMonthOfYear		= UI_HTML_Elements::Options( $optMonthOfYear );

$optMinutes	= [];
for( $i=0; $i<=59; $i++ )
	$optMinutes[$i]	= $i;
$optMinutes		= UI_HTML_Elements::Options( $optMinutes );

$optHour	= [];
for( $i=0; $i<=23; $i++ )
	$optHour[$i]	= $i;
$optHour		= UI_HTML_Elements::Options( $optHour );

$optWeekday	= array(
	1	=> 'Montag',
	2	=> 'Dienstag',
	3	=> 'Mittwoch',
	4	=> 'Donnerstag',
	5	=> 'Freitag',
	6	=> 'Samstag',
	7	=> 'Sonntag',
);
$optWeekday		= UI_HTML_Elements::Options( $optWeekday );

$optDay	= [];
for( $i=1; $i<=31; $i++ )
	$optDay[$i]	= $i;
$optDay		= UI_HTML_Elements::Options( $optDay );


$optMonth	= array(
	1		=> 'Januar',
	2		=> 'Februar',
	3		=> 'März',
	4		=> 'April',
	5		=> 'Mai',
	6		=> 'Juni',
	7		=> 'Juli',
	8		=> 'August',
	9		=> 'September',
	10		=> 'Oktober',
	11		=> 'November',
	12		=> 'Dezember',
);
$optMonth		= UI_HTML_Elements::Options( $optMonth );

$optReportMode		= UI_HTML_Elements::Options( $wordsGeneral['job-schedule-report-modes'] );
$optReportChannel	= UI_HTML_Elements::Options( $wordsGeneral['job-schedule-report-channels'] );



$script	= '
jQuery(document).ready(function(){
	ModuleManageJobSchedule.valuesSelected.minute = '.json_encode( array_keys( $words['options-minuteOfHour'] ) ).';
	ModuleManageJobSchedule.valuesSelected.hour = '.json_encode( array_keys( $words['options-hourOfDay'] ) ).';
	ModuleManageJobSchedule.valuesSelected.day = '.json_encode( array_keys( $words['options-dayOfMonth'] ) ).';
	ModuleManageJobSchedule.valuesSelected.month = '.json_encode( array_keys( $words['options-monthOfYear'] ) ).';
	ModuleManageJobSchedule.valuesSelected.weekday = '.json_encode( array_keys( $words['options-dayOfWeek'] ) ).';
	ModuleManageJobSchedule.init();
//	var exp = "*/2 */3 1-4 */6 1,2,3,4,5";
	var exp = "9-10 7-8 5-6 3-4 1-2";
	ModuleManageJobSchedule.applyCronExpressionToInputs(exp);

	var exp = "P1Y2M3DT4H5M";
	ModuleManageJobSchedule.applyIntervalExpressionToInputs(exp);

	var exp = "2020-08-08 01:00";
	ModuleManageJobSchedule.applyDatetimeExpressionToInputs(exp);
});
';

$form		= UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', $words['add']['labelJobDefinitionId'], array( 'for' => 'input_jobDefinitionId' ) ),
			UI_HTML_Tag::create( 'select', $optDefinition, array(
				'id'		=> 'input_jobDefinitionId',
				'name'		=> 'jobDefinitionId',
				'class'		=> 'span12',
			) ),
		), array( 'class' => 'span4' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', $words['add']['labelArguments'], array( 'for' => 'input_arguments' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'id'		=> 'input_arguments',
				'name'		=> 'arguments',
				'class'		=> 'span12',
			) ),
		), array( 'class' => 'span8' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', $words['add']['labelTitle'], array( 'for' => 'input_title' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'id'		=> 'input_title',
				'name'		=> 'title',
				'class'		=> 'span12',
			) ),
		), array( 'class' => 'span7' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Eingabeformat', array( 'for' => 'input_format' ) ),
			UI_HTML_Tag::create( 'select', $optFormat, array(
				'name'	=> 'format',
				'id'	=> 'input_format',
				'class'	=> 'span12 has-optionals modifiesCronExpression',
			) ),
		), array( 'class' => 'span3' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', $words['add']['labelStatus'], array( 'for' => 'input_status' ) ),
			UI_HTML_Tag::create( 'select', $optStatus, array(
				'id'		=> 'input_status',
				'name'		=> 'status',
				'class'		=> 'span12',
			) ),
		), array( 'class' => 'span2' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array( '<hr/>',
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'div', array(
								UI_HTML_Tag::create( 'h4', 'Zeit' ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', $words['add']['labelHourOfDay'], array( 'for' => 'input_hourOfDay' ) ),
									UI_HTML_Tag::create( 'select', $optHourOfDay, array(
										'id'		=> 'input_hourOfDay',
										'name'		=> 'hourOfDay',
										'class'		=> 'span12 canHaveValue canHaveRange canHaveValues modifiesCronExpression',
									) ),
								), array( 'class' => 'span6' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'Wert', array( 'for' => 'input_hourOfDay_value' ) ),
									UI_HTML_Tag::create( 'select', $optHour, array(
										'id'		=> 'input_hourOfDay_value',
										'name'		=> 'hourOfDayValue',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span6 span_input_value' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'von', array( 'for' => 'input_hourOfDay_rangeFrom' ) ),
									UI_HTML_Tag::create( 'select', $optHour, array(
										'id'		=> 'input_hourOfDay_rangeFrom',
										'name'		=> 'hourOfDayRangeFrom',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_range' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'bis', array( 'for' => 'input_hourOfDay_rangeTo' ) ),
									UI_HTML_Tag::create( 'select', $optHour, array(
										'id'		=> 'input_hourOfDay_rangeTo',
										'name'		=> 'hourOfDayRangeTo',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_range' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'Werte <small class="muted">(kommagetrennt)</small>', array( 'for' => 'input_hourOfDay_values' ) ),
									UI_HTML_Tag::create( 'input', '', array(
										'type'		=> 'text',
										'id'		=> 'input_hourOfDay_values',
										'name'		=> 'hourOfDayValues',
										'class'		=> 'span12 modifiesCronExpression',
										'value'		=> '0',
									) ),
								), array( 'class' => 'span6 span_input_values' ) ),
							), array( 'class' => 'row-fluid' ) ),
						), array( 'class' => 'span5' ) ),
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'h4', 'Datum' ),
							UI_HTML_Tag::create( 'div', array(
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', $words['add']['labelMonthOfYear'], array( 'for' => 'input_monthOfYear' ) ),
									UI_HTML_Tag::create( 'select', $optMonthOfYear, array(
										'id'		=> 'input_monthOfYear',
										'name'		=> 'monthOfYear',
										'class'		=> 'span12 canHaveValue canHaveRange canHaveValues modifiesCronExpression',
									) ),
								), array( 'class' => 'span6' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'Wert', array( 'for' => 'input_monthOfYear_value' ) ),
									UI_HTML_Tag::create( 'select', $optMonth, array(
										'id'		=> 'input_monthOfYear_value',
										'name'		=> 'monthOfYearValue',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span6 span_input_value' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'von', array( 'for' => 'input_monthOfYear_rangeFrom' ) ),
									UI_HTML_Tag::create( 'select', $optMonth, array(
										'id'		=> 'input_monthOfYear_rangeFrom',
										'name'		=> 'monthOfYearRangeFrom',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_range' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'bis', array( 'for' => 'input_monthOfYear_rangeTo' ) ),
									UI_HTML_Tag::create( 'select', $optMonth, array(
										'id'		=> 'input_monthOfYear_rangeTo',
										'name'		=> 'monthOfYearRangeTo',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_range' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'Werte <small class="muted">(kommagetrennt)</small>', array( 'for' => 'input_monthOfYear_values' ) ),
									UI_HTML_Tag::create( 'input', '', array(
										'type'		=> 'text',
										'id'		=> 'input_monthOfYear_values',
										'name'		=> 'monthOfYearValues',
										'class'		=> 'span12 modifiesCronExpression',
										'value'		=> '0',
									) ),
								), array( 'class' => 'span6 span_input_values' ) ),
							), array( 'class' => 'row-fluid' ) ),
						), array( 'class' => 'span5 offset1' ) ),
					), array( 'class' => 'row-fluid' ) ),
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'div', array(
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', $words['add']['labelMinuteOfHour'], array( 'for' => 'input_minuteOfHour' ) ),
									UI_HTML_Tag::create( 'select', $optMinuteOfHour, array(
										'id'		=> 'input_minuteOfHour',
										'name'		=> 'minuteOfHour',
										'class'		=> 'span12 canHaveValue canHaveRange canHaveValues modifiesCronExpression',
									) ),
								), array( 'class' => 'span6' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'Wert', array( 'for' => 'input_minuteOfHour_value' ) ),
									UI_HTML_Tag::create( 'select', $optMinutes, array(
										'id'		=> 'input_minuteOfHour_value',
										'name'		=> 'minuteOfHourValue',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span6 span_input_value' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'von', array( 'for' => 'input_minuteOfHour_rangeFrom' ) ),
									UI_HTML_Tag::create( 'select', $optMinutes, array(
										'id'		=> 'input_minuteOfHour_rangeFrom',
										'name'		=> 'minuteOfHourRangeFrom',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_range' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'bis', array( 'for' => 'input_minuteOfHour_rangeTo' ) ),
									UI_HTML_Tag::create( 'select', $optMinutes, array(
										'id'		=> 'input_minuteOfHour_rangeTo',
										'name'		=> 'minuteOfHourRangeTo',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_range' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'Werte <small class="muted">(kommagetrennt)</small>', array( 'for' => 'input_minuteOfHour_values' ) ),
									UI_HTML_Tag::create( 'input', '', array(
										'type'		=> 'text',
										'id'		=> 'input_minuteOfHour_values',
										'name'		=> 'minuteOfHourValues',
										'class'		=> 'span12 modifiesCronExpression',
										'value'		=> '0',
									) ),
								), array( 'class' => 'span6 span_input_values' ) ),
							), array( 'class' => 'row-fluid' ) ),
						), array( 'class' => 'span5' ) ),
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'div', array(
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', $words['add']['labelDayOfWeek'], array( 'for' => 'input_dayOfWeek' ) ),
									UI_HTML_Tag::create( 'select', $optDayOfWeek, array(
										'id'		=> 'input_dayOfWeek',
										'name'		=> 'dayOfWeek',
										'class'		=> 'span12 canHaveValue canHaveRange canHaveValues modifiesCronExpression',
									) ),
								), array( 'class' => 'span6' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'Wert', array( 'for' => 'input_dayOfWeek_value' ) ),
									UI_HTML_Tag::create( 'select', $optWeekday, array(
										'id'		=> 'input_dayOfWeek_value',
										'name'		=> 'dayOfWeekValue',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span6 span_input_value' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'von', array( 'for' => 'input_dayOfWeek_rangeFrom' ) ),
									UI_HTML_Tag::create( 'select', $optWeekday, array(
										'id'		=> 'input_dayOfWeek_rangeFrom',
										'name'		=> 'dayOfWeekRangeFrom',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_range' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'bis', array( 'for' => 'input_dayOfWeek_rangeTo' ) ),
									UI_HTML_Tag::create( 'select', $optWeekday, array(
										'id'		=> 'input_dayOfWeek_rangeTo',
										'name'		=> 'dayOfWeekRangeTo',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_range' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'Werte <small class="muted">(kommagetrennt)</small>', array( 'for' => 'input_dayOfWeek_value' ) ),
									UI_HTML_Tag::create( 'input', '', array(
										'type'		=> 'text',
										'id'		=> 'input_dayOfWeek_values',
										'name'		=> 'dayOfWeekValues',
										'class'		=> 'span12 modifiesCronExpression',
										'value'		=> '0',
									) ),
								), array( 'class' => 'span6 span_input_values' ) ),
							), array( 'class' => 'row-fluid' ) ),
						), array( 'class' => 'span5 offset1 optional format format-cron-week' ) ),
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'div', array(
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', $words['add']['labelDayOfMonth'], array( 'for' => 'input_dayOfMonth' ) ),
									UI_HTML_Tag::create( 'select', $optDayOfMonth, array(
										'id'		=> 'input_dayOfMonth',
										'name'		=> 'dayOfMonth',
										'class'		=> 'span12 canHaveValue canHaveRange canHaveValues modifiesCronExpression',
									) ),
								), array( 'class' => 'span6' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'Wert', array( 'for' => 'input_dayOfMonth_value' ) ),
									UI_HTML_Tag::create( 'select', $optDay, array(
										'id'		=> 'input_dayOfMonth_value',
										'name'		=> 'dayOfMonthValue',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_value' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'von', array( 'for' => 'input_dayOfMonth_rangeFrom' ) ),
									UI_HTML_Tag::create( 'select', $optDay, array(
										'id'		=> 'input_dayOfMonth_rangeFrom',
										'name'		=> 'dayOfMonthRangeFrom',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_range' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'bis', array( 'for' => 'input_dayOfMonth_rangeTo' ) ),
									UI_HTML_Tag::create( 'select', $optDay, array(
										'id'		=> 'input_dayOfMonth_rangeTo',
										'name'		=> 'dayOfMonthRangeTo',
										'class'		=> 'span12 modifiesCronExpression',
									) ),
								), array( 'class' => 'span3 span_input_range' ) ),
								UI_HTML_Tag::create( 'div', array(
									UI_HTML_Tag::create( 'label', 'Werte <small class="muted">(kommagetrennt)</small>', array( 'for' => 'input_dayOfMonth_values' ) ),
									UI_HTML_Tag::create( 'input', '', array(
										'type'		=> 'text',
										'id'		=> 'input_dayOfMonth_values',
										'name'		=> 'dayOfMonthValues',
										'class'		=> 'span12 modifiesCronExpression',
										'value'		=> '0',
									) ),
								), array( 'class' => 'span6 span_input_values' ) ),
							), array( 'class' => 'row-fluid' ) ),
						), array( 'class' => 'span5 offset1 optional format format-cron-month' ) ),
					), array( 'class' => 'row-fluid' ) ),
				), array( 'class' => 'span8' ) ),
				UI_HTML_Tag::create( 'div', array(
					'<blockquote>
						<label>Wert</label>
						<div id="container_expression_cron" class="label_dateCode"><em class="muted">wird berechnet</em></div>
						<br/>
					</blockquote>',
				), array( 'class' => 'span4' ) ),
			), array( 'class' => 'row-fluid' ) ),
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid optional format format-cron-month format format-cron-week' ) ),

	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'label', $words['add']['labelYears'], array( 'for' => 'input_years' ) ),
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'		=> 'number',
								'id'		=> 'input_years',
								'name'		=> 'years',
								'class'		=> 'span12 modifiesIntervalExpression',
								'value'		=> 0,
								'min'		=> 0,
								'max'		=> 10,
								'step'		=> 1,
							) ),
						), array( 'class' => 'span2' ) ),
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'label', $words['add']['labelMonths'], array( 'for' => 'input_months' ) ),
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'		=> 'number',
								'id'		=> 'input_months',
								'name'		=> 'months',
								'class'		=> 'span12 modifiesIntervalExpression',
								'value'		=> 0,
								'min'		=> 0,
								'max'		=> 12,
								'step'		=> 1,
							) ),
						), array( 'class' => 'span2' ) ),
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'label', $words['add']['labelDays'], array( 'for' => 'input_days' ) ),
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'		=> 'number',
								'id'		=> 'input_days',
								'name'		=> 'days',
								'class'		=> 'span12 modifiesIntervalExpression',
								'value'		=> 0,
								'min'		=> 0,
								'max'		=> 30,
								'step'		=> 1,
							) ),
						), array( 'class' => 'span2' ) ),
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'label', $words['add']['labelHours'], array( 'for' => 'input_hours' ) ),
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'		=> 'number',
								'id'		=> 'input_hours',
								'name'		=> 'hours',
								'class'		=> 'span12 modifiesIntervalExpression',
								'value'		=> 0,
								'min'		=> 0,
								'max'		=> 23,
								'step'		=> 1,
							) ),
						), array( 'class' => 'span2' ) ),
						UI_HTML_Tag::create( 'div', array(
							UI_HTML_Tag::create( 'label', $words['add']['labelMinutes'], array( 'for' => 'input_minutes' ) ),
							UI_HTML_Tag::create( 'input', NULL, array(
								'type'		=> 'number',
								'id'		=> 'input_minutes',
								'name'		=> 'minutes',
								'class'		=> 'span12 modifiesIntervalExpression',
								'value'		=> 0,
								'min'		=> 0,
								'max'		=> 59,
								'step'		=> 1,
							) ),
						), array( 'class' => 'span2' ) ),
					), array( 'class' => 'row-fluid' ) ),
				), array( 'class' => 'span8' ) ),
				UI_HTML_Tag::create( 'div', array(
					'<blockquote>
						<label>Wert</label>
						<div id="container_expression_interval" class="label_dateCode"><em class="muted">wird berechnet</em></div>
						<br/>
					</blockquote>',
				), array( 'class' => 'span4' ) ),
			), array( 'class' => 'row-fluid' ) ),
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid optional format format-interval' ) ),

	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', $words['add']['labelDate'], array( 'for' => 'input_date' ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> 'date',
						'id'		=> 'input_date',
						'name'		=> 'date',
						'class'		=> 'span12 modifiesDatetimeExpression',
					) ),
				), array( 'class' => 'span3' ) ),
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', $words['add']['labelTime'], array( 'for' => 'input_time' ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> 'time',
						'id'		=> 'input_time',
						'name'		=> 'time',
						'class'		=> 'span12 modifiesDatetimeExpression',
					) ),
				), array( 'class' => 'span3' ) ),
				UI_HTML_Tag::create( 'div', array(
					'<blockquote>
						<label>Wert</label>
						<div id="container_expression_datetime" class="label_dateCode"><em class="muted">wird berechnet</em></div>
						<br/>
					</blockquote>',
				), array( 'class' => 'span4 offset2' ) ),
			), array( 'class' => 'row-fluid' ) ),
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid optional format format-datetime' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array( '<hr/>',
		), array( 'class' => 'span12' ) ),
	), array( 'class' => 'row-fluid' ) ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Report-Modus', array( 'for' => 'input_reportMode' ) ),
			UI_HTML_Tag::create( 'select', $optReportMode, array(
				'id'		=> 'input_reportMode',
				'name'		=> 'reportMode',
				'class'		=> 'span12 has-optionals',
			) ),
		), array( 'class' => 'span3' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Report-Kanal', array( 'for' => 'input_reportChannel' ) ),
			UI_HTML_Tag::create( 'select', $optReportChannel, array(
				'id'		=> 'input_reportChannel',
				'name'		=> 'reportChannel',
				'class'		=> 'span12 has-optionals',
			) ),
		), array( 'class' => 'span2 optional reportMode reportMode-1 reportMode-2 reportMode-3 reportMode-4 reportMode-5' ) ),
		UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'label', 'Report-Empfänger <small class="muted">(kommagetrennt)</small>', array( 'for' => 'input_reportReceivers' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'id'		=> 'input_reportReceivers',
				'name'		=> 'reportReceivers',
				'class'		=> 'span12',
			) ),
		), array( 'class' => 'span7 optional reportChannel reportChannel-1 reportChannel-2' ) ),
	), array( 'class' => 'row-fluid' ) ),
) );

$buttons	= UI_HTML_Tag::create( 'div', $buttonCancel.' '.$buttonAdd, array() );

$tabs		= View_Manage_Job::renderTabs( $env, 'schedule' );

$env->getPage()->js->addScriptOnReady( $script );

return $tabs.UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $words['add']['heading'] ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'	=> 'hidden',
				'value'	=> '',
				'id'	=> 'input_expressionCron',
				'name'	=> 'expressionCron',
			) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'	=> 'hidden',
				'value'	=> '',
				'id'	=> 'input_expressionInterval',
				'name'	=> 'expressionInterval',
			) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'	=> 'hidden',
				'value'	=> '',
				'id'	=> 'input_expressionDatetime',
				'name'	=> 'expressionDatetime',
			) ),
			$form,
			UI_HTML_Tag::create( 'div', array(
				$buttons,
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './manage/job/schedule/add', 'method' => 'post', 'id' => 'formManageJobScheduleAdd' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
