<?php

$w	= (object) $words['index'];

if( $filterTypes === NULL )
	$filterTypes	= array(
		Model_Mission::TYPE_TASK,
		Model_Mission::TYPE_EVENT,
	);

for( $i=0; $i<2; $i++){
	$attributes	= array(
		'type'		=> 'checkbox',
		'name'		=> 'types[]',
		'value'		=> $i,
		'id'		=> 'filter_type_'.$i,
		'class'		=> 'filter-type',
		'checked'	=> in_array( $i, $filterTypes ) ? 'checked' : NULL,
	);
	if( !( count( $filterTypes ) == 1 && $filterTypes[0] == $i ) )
		$attributes['onchange']	= 'this.form.submit();';
	$inputType[$i]	= UI_HTML_Tag::create( 'input', NULL, $attributes );
}

if( $filterStates === NULL )
	$filterStates	= array( 0, 1, 2, 3 );

for( $i=0; $i<=3; $i++){
	$attributes	= array(
		'type'		=> 'checkbox',
		'name'		=> 'states[]',
		'value'		=> $i,
		'id'		=> 'filter_status_'.$i,
		'class'		=> 'filter-status',
		'checked'	=> in_array( $i, $filterStates ) ? 'checked' : NULL,
	);
	if( !( count( $filterStates ) == 1 && $filterStates[0] == $i ) )
		$attributes['onchange']	= 'this.form.submit();';
	$inputStatus[$i]	= UI_HTML_Tag::create( 'input', NULL, $attributes );
}

//  --  FILTER  --  //
$optOrder	= array( '' => '' ) + $words['filter-orders'];
$optOrder	= UI_HTML_Elements::Options( $optOrder, $session->get( 'filter_mission_order' ) );

$optDirection	= array( '' => '' ) + $words['filter-directions'];
$optDirection	= UI_HTML_Elements::Options( $optDirection, $session->get( 'filter_mission_direction' ) );

$iconUp		= UI_HTML_Elements::Image( 'http://icons.ceusmedia.de/famfamfam/silk/arrow_up.png', $words['filter-directions']['ASC'] );
$iconDown	= UI_HTML_Elements::Image( 'http://icons.ceusmedia.de/famfamfam/silk/arrow_down.png', $words['filter-directions']['DESC'] );
$iconRight	= UI_HTML_Elements::Image( 'http://icons.ceusmedia.de/famfamfam/silk/arrow_right.png', $words['list-actions']['moveRight'] );
$iconLeft	= UI_HTML_Elements::Image( 'http://icons.ceusmedia.de/famfamfam/silk/arrow_left.png', $words['list-actions']['moveLeft'] );
$iconEdit	= UI_HTML_Elements::Image( 'http://icons.ceusmedia.de/famfamfam/silk/pencil.png', $words['list-actions']['edit'] );
$iconRemove	= UI_HTML_Elements::Image( 'http://icons.ceusmedia.de/famfamfam/silk/bin_closed.png', $words['list-actions']['remove'] );

$disabled	= $session->get( 'filter_mission_direction' ) == 'ASC';
$buttonUp	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=ASC', $iconUp, 'tiny', NULL, $disabled );
$buttonDown	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=DESC', $iconDown, 'tiny', NULL, !$disabled );


$panelFilter	= '
<form action="./work/mission/filter" method="post">
	<fieldset>
		<legend>Filter</legend>
		<ul class="input">
			<li>
				<label for="filter_query"><strike>'.$w->labelQuery.'</strike></label><br/>
				<input name="query" id="filter_query" value="'.$session->get( 'filter_mission_query' ).'" class="max"/>
			</li>
			<li>
				<b><label>Missionstypen</label></b><br/>
				<ul style="margin: 0px; padding: 0px; list-style: none">
					<li>
						<label for="filter_type_0">
							'.$inputType[0].'
							&nbsp;Aufgaben
						</label>
					</li>
					<li>
						<label for="filter_type_1">
							'.$inputType[1].'
							&nbsp;Termine
						</label>
					</li>
				</ul>
			</li>
			<li>
				<b><label>Zustände</label></b><br/>
				<ul style="margin: 0px; padding: 0px; list-style: none">
					<li>
						<label for="filter_status_0">
							'.$inputStatus[0].'
							&nbsp;'.$words['states'][0].'
						</label>
					</li>
					<li>
						<label for="filter_status_1">
							'.$inputStatus[1].'
							&nbsp;'.$words['states'][1].'
						</label>
					</li>
					<li>
						<label for="filter_status_2">
							'.$inputStatus[2].'
							&nbsp;'.$words['states'][2].'
						</label>
					</li>
					<li>
						<label for="filter_status_3">
							'.$inputStatus[3].'
							&nbsp;'.$words['states'][3].'
						</label>
					</li>
				</ul>
			</li>
			<li>
				<label for="filter_order">'.$w->labelOrder.'</label><br/>
				<div class="column-left-70">
					<select name="order" id="filter_order" class="max" onchange="this.form.submit();">'.$optOrder.'</select>
				</div>
				<div class="column-right-30">
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
</form>
';
return $panelFilter;
?>