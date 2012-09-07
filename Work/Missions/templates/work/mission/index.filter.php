<?php

$w	= (object) $words['index'];

//  --  FILTER: TYPES  --  //
if( $filterTypes === NULL )
	$filterTypes	= array(
		Model_Mission::TYPE_TASK,
		Model_Mission::TYPE_EVENT,
	);

$enabledTypes	= count( $filterTypes ) != 2;
$attributes		= array(
	'type'				=> 'checkbox',
	'name'				=> 'type',
	'id'				=> 'switch_type',
	'class'				=> 'optional-trigger',
	'onchange'			=> 'showOptionals(this);',
	'data-animation'	=> 'slide',
	'data-speed-show'	=> 500,
	'data-speed-hide'	=> 300,
	'checked'			=> $enabledTypes ? 'checked' : NULL,
);
$inputSwitchType	= UI_HTML_Tag::create( 'input', NULL, $attributes );

$list	= array();
for( $i=0; $i<2; $i++){
	$id		= 'filter_type_'.$i;
	$attributes	= array(
		'type'		=> 'checkbox',
		'name'		=> 'types[]',
		'value'		=> $i,
		'id'		=> $id,
		'class'		=> 'filter-type',
		'checked'	=> in_array( $i, $filterTypes ) ? 'checked' : NULL,
	);
	if( !( count( $filterTypes ) == 1 && $filterTypes[0] == $i ) )
		$attributes['onchange']	= 'this.form.submit();';
	$input	= UI_HTML_Tag::create( 'input', NULL, $attributes );
	$label	= $words['types'][$i];
	$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$label, array( 'for' => $id ) );
	$list[]	= UI_HTML_Tag::create( 'li', $label );
}
$attributes	= array(
	'style'		=> 'margin: 0px; padding: 0px; list-style: none; display: '.( $enabledTypes ? 'block' : 'none' ),
	'class'		=> 'optional type type-true'
);
$optListTypes	= UI_HTML_Tag::create( 'ul', join( $list ), $attributes );


//  --  FILTER: PRIORITIES  --  //
if( $filterPriorities === NULL )
	$filterPriorities	= array( 0, 1, 2, 3, 4, 5 );

$enabledPriorities	= count( $filterPriorities ) != 6;
$attributes			= array(
	'type'				=> 'checkbox',
	'name'				=> 'priority',
	'id'				=> 'switch_priority',
	'class'				=> 'optional-trigger',
	'onchange'			=> 'showOptionals(this);',
	'data-animation'	=> 'slide',
	'data-speed-show'	=> 500,
	'data-speed-hide'	=> 300,
	'checked'			=> $enabledPriorities ? 'checked' : NULL,
);
$inputSwitchPriority	= UI_HTML_Tag::create( 'input', NULL, $attributes );

$list	= array();
for( $i=0; $i<6; $i++){
	$id		= 'filter_priority_'.$i;
	$attributes	= array(
		'type'		=> 'checkbox',
		'name'		=> 'priorities[]',
		'value'		=> $i,
		'id'		=> $id,
		'class'		=> 'filter-priority',
		'checked'	=> in_array( $i, $filterPriorities ) ? 'checked' : NULL,
	);
	if( !( count( $filterPriorities ) == 1 && $filterPriorities[0] == $i ) )
		$attributes['onchange']	= 'this.form.submit();';
	$input	= UI_HTML_Tag::create( 'input', NULL, $attributes );
	$label	= $words['priorities'][$i];
	$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$label, array( 'for' => $id ) );
	$list[]	= UI_HTML_Tag::create( 'li', $label );
}
$attributes	= array(
	'style'		=> 'margin: 0px; padding: 0px; list-style: none; display: '.( $enabledPriorities ? 'block' : 'none' ),
	'class'		=> 'optional priority priority-true'
);
$optListPriorities	= UI_HTML_Tag::create( 'ul', join( $list ), $attributes );


//  --  FILTER: STATES  --  //
if( $filterStates === NULL )
	$filterStates	= array( 0, 1, 2, 3 );

$enabledStates	= count( $filterStates ) != 4;
$attributes		= array(
	'type'				=> 'checkbox',
	'name'				=> 'status',
	'id'				=> 'switch_status',
	'class'				=> 'optional-trigger',
	'onchange'			=> 'showOptionals(this);',
	'data-animation'	=> 'slide',
	'data-speed-show'	=> 500,
	'data-speed-hide'	=> 300,
	'checked'			=> $enabledStates ? 'checked' : NULL,
);
$inputSwitchStatus	= UI_HTML_Tag::create( 'input', NULL, $attributes );

$list	= array();
for( $i=0; $i<5; $i++){
	$id		= 'filter_status_'.$i;
	$attributes	= array(
		'type'		=> 'checkbox',
		'name'		=> 'states[]',
		'value'		=> $i,
		'id'		=> $id,
		'class'		=> 'filter-status',
		'checked'	=> in_array( $i, $filterStates ) ? 'checked' : NULL,
	);
	if( !( count( $filterStates ) == 1 && $filterStates[0] == $i ) )
		$attributes['onchange']	= 'this.form.submit();';
	$input	= UI_HTML_Tag::create( 'input', NULL, $attributes );
	$label	= $words['states'][$i];
	$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$label, array( 'for' => $id ) );
	$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'id' => 'filter_status_item_'.$i ) );
}
$attributes	= array(
	'style'		=> 'margin: 0px; padding: 0px; list-style: none; display: '.( $enabledStates ? 'block' : 'none' ),
	'class'		=> 'optional status status-true'
);
$optListStates	= UI_HTML_Tag::create( 'ul', join( $list ), $attributes );


//  --  FILTER: ORDER & DIRECTION  --  //
$optOrder	= $words['filter-orders'];
$optOrder	= UI_HTML_Elements::Options( $optOrder, $session->get( 'filter_mission_order' ) );

$iconUp		= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/arrow_up.png', $words['filter-directions']['ASC'] );
$iconDown	= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/arrow_down.png', $words['filter-directions']['DESC'] );

$disabled	= $session->get( 'filter_mission_direction' ) == 'ASC';
$buttonUp	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=ASC', $iconUp, 'tiny', NULL, $disabled );
$buttonDown	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=DESC', $iconDown, 'tiny', NULL, !$disabled );

$wordsAccess	= array( 'owner' => 'meine', 'worker' => 'mir zugewiesen' );

$optAccess	= UI_HTML_Elements::Options( $wordsAccess, $session->get( 'filter_mission_access' ) );

$optView	= array(
	'0' => 'ausstehend',
	'1' => 'geschlossen',
);
$optView	= UI_HTML_Elements::Options( $optView, $filterStates == array( 4 ) ? 1 : 0 );

$panelFilter	= '
<script>
$(document).ready(function(){
	FormMissionFilter.__init();
	if(!parseInt($("#switch_view").val()))
		$("li.filter_status").show();
});
</script>
<form id="form_mission_filter" action="./work/mission/filter?reset" method="post">
	<fieldset>
		<legend class="icon filter">Filter</legend>
		<ul class="input">
			<li>
				<label for="filter_query">'.$w->labelQuery.'</label><br/>
				<div style="position: relative; display: none;" id="reset-button-container">
					<img id="reset-button-trigger" src="themes/custom/img/clearSearch.png" style="position: absolute; right: 3%; top: 9px; cursor: pointer"/>
				</div>
				<input name="query" id="filter_query" value="'.$session->get( 'filter_mission_query' ).'" class="max"/>
			</li>
<!--			<li>
				<label for="filter_access">???</label><br/>
				<select name="access" id="filter_access" class="max" onchange="this.form.submit();">'.$optAccess.'</select>
			</li>-->
			<li>
				<label for="switch_type" style="font-weight: bold">
					'.$inputSwitchType.'
					<span>Missionstypen</span>
				</label><br/>
				'.$optListTypes.'
			</li>
			<li>
				<label for="switch_priority" style="font-weight: bold">
					'.$inputSwitchPriority.'
					<span>Prioritäten</span>
				</label><br/>
				'.$optListPriorities.'
			</li>
			<li>
				<label for="switch_view" style="">Sichtweise</label><br/>
				<select name="view" id="switch_view" onchange="FormMissionFilter.changeView(this);" class="max">'.$optView.'</select>
		
			</li>
			<li class="filter_status" style="display: none">
				<label for="switch_status" style="font-weight: bold">
					'.$inputSwitchStatus.'
					<span>Zustände</span>
				</label><br/>
				'.$optListStates.'
			</li>
			<li>
				<label for="filter_order">'.$w->labelOrder.'</label><br/>
				<div class="column-left-60">
					<select name="order" id="filter_order" class="max" onchange="this.form.submit();">'.$optOrder.'</select>
				</div>
				<div class="column-right-40">
					'.$buttonUp.$buttonDown.'
				</div>
				<div class="column-clear"></div>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'filter', $w->buttonFilter, 'button filter' ).'
			'.UI_HTML_Elements::LinkButton( './work/mission/filter?reset', $w->buttonReset, 'button reset' ).'
		</div>
	</fieldset>
</form>';
return $panelFilter;
?>