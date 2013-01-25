<?php

$w	= (object) $words['index'];

class View_Helper_MissionFilter{

	protected $options	= array(
		'animation'	=> 'slide',
		'speedShow'	=> 500,
		'speedHide'	=> 300
	);

	public function __construct( $env ){
		$this->env	= $env;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$name			...
	 *	@param		array		$options		...
	 *	@param		array		$selection		...
	 *	@param		class		$class			...
	 *	@param		array		$data			...
	 *	@param		string		$dataKey		...
	 *	@param		integer		$orderBy		0 - none, 1 - by option key, 2 - by option label
	 *	@return		array		List of rendered filter list options
	 */
	function renderFilterOptions( $name, $options, $selection, $class = "", $data = array(), $dataKey = NULL, $orderBy = NULL ){
		$index	= array();
		$list	= array();
		foreach( $options as $optionKey => $optionLabel ){
			$id	= array_shift( explode( " ", $class ) );
			$id	= preg_replace( "/[^a-z0-9]/", "_", $id ).'_'.$optionKey;
			$count	= 0;
			if( $data && $dataKey ){
				foreach( $data as $item )
					$count += $item->$dataKey == $optionKey ? 1 : 0;
			}
			$isChecked	= in_array( $optionKey, $selection );
			$classes	= array( $class );
			if( $isChecked )
				$classes[]	= 'selected';
			$attributes	= array(
				'type'		=> 'checkbox',
				'name'		=> $name.'[]',
				'value'		=> $optionKey,
				'id'		=> $id,
				'class'		=> $class,
				'checked'	=> $isChecked ? 'checked' : NULL,
			);
			if( !( count( $options ) == 1 && $selection[0] == $optionKey ) )
				$attributes['onchange']	= 'this.form.submit();';
			$input	= UI_HTML_Tag::create( 'input', NULL, $attributes );
			$label	= $optionLabel;
			if( $count ){
				$label		= $label.' <small>('.$count.')</small>';
				$classes[]	= 'used';
			}
			else{
				$label	= '<span class="empty">'.$label.'</span>';
				$classes[]	= 'empty';
			}
			$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$label, array( 'for' => $id ) );
			$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => implode( ' ', $classes ) ) );
			if( $orderBy ){
				$orderValue = $orderBy == 1 ? $optionKey : $optionLabel;
				$index[count( $list ) - 1]	= $orderValue;
			}
		}
		if( $orderBy ){
			$list2	= array();
			natcasesort( $index );
			foreach( array_keys( $index ) as $nr )
				$list2[]	= $list[$nr];
			$list	= $list2;
		}
		return $list;
	}

	public function renderCheckboxFilter( $id, $name, $class, $optionName, $optionsMap, $optionSelection, $optionData = NULL, $optionDataKey = NULL, $optionClass = NULL, $optionOrder = NULL ){
		$isOpen	=  count( $optionsMap ) != count( $optionSelection );
		$attributes		= array(
			'type'				=> 'checkbox',
			'name'				=> $name,
			'id'				=> $id,
			'class'				=> 'optional-trigger',
			'onchange'			=> 'showOptionals(this);',
			'data-animation'	=> $this->options['animation'],
			'data-speed-show'	=> $this->options['speedShow'],
			'data-speed-hide'	=> $this->options['speedHide'],
			'checked'			=> $isOpen ? 'checked' : NULL,
		);
		$options	= $this->renderFilterOptions( $optionName, $optionsMap, $optionSelection, $optionClass, $optionData, $optionDataKey, 0 );
		if( count( $options ) > 1 ){
			$switch		= UI_HTML_Tag::create( 'input', NULL, $attributes );
			$attributes	= array(
				'style'		=> 'margin: 0px; padding: 0px; list-style: none; display: '.( $isOpen ? 'block' : 'none' ),
				'class'		=> 'optional '.$name.' '.$name.'-true'
			);
			$options	= UI_HTML_Tag::create( 'ul', $options, $attributes );
			return array( $switch, $options );
		}
		return array( '', '' );
	}
}

$helperFilter	= new View_Helper_MissionFilter( $this->env );


//  --  FILTER: TYPES  --  //
if( $filterTypes === NULL )
	$filterTypes	= array(
		Model_Mission::TYPE_TASK,
		Model_Mission::TYPE_EVENT,
	);

$a	= $helperFilter->renderCheckboxFilter( 'switch_type', 'type', NULL, 'types', $words['types'], $filterTypes, $missions, 'type', 'filter-type' );
#print_m( $a );
#die;
$inputSwitchType	= $a[0];
$optListTypes		= $a[1];


/*$enabledTypes	= count( $filterTypes ) != 2;
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
$optType			= $helperFilter->renderFilterOptions( 'types', $words['types'], $filterTypes, 'filter-type', $missions, 'type' );
if( count( $optType ) > 1 ){
	$attributes	= array(
		'style'		=> 'margin: 0px; padding: 0px; list-style: none; display: '.( $enabledTypes ? 'block' : 'none' ),
		'class'		=> 'optional type type-true'
	);
	$optListTypes	= UI_HTML_Tag::create( 'ul', $optType, $attributes );
	$filters[]	= "";
}
*/
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
$optPriority		= $helperFilter->renderFilterOptions( 'priorities', $words['priorities'], $filterPriorities, 'filter-priority', $missions, 'priority' );
if( count( $optPriority ) > 1 ){
	$attributes	= array(
		'style'		=> 'margin: 0px; padding: 0px; list-style: none; display: '.( $enabledPriorities ? 'block' : 'none' ),
		'class'		=> 'optional priority priority-true'
	);
	$optListPriorities	= UI_HTML_Tag::create( 'ul', join( $optPriority ), $attributes );
	$filters[]	= "";
}


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

$optStatus			= $helperFilter->renderFilterOptions( 'states', $words['states'], $filterStates, 'filter-status', $missions, 'status' );
if( count( $optStatus ) > 1 ){
	$attributes	= array(
		'style'		=> 'margin: 0px; padding: 0px; list-style: none; display: '.( $enabledStates ? 'block' : 'none' ),
		'class'		=> 'optional status status-true'
	);
	$optListStates	= UI_HTML_Tag::create( 'ul', join( $optStatus ), $attributes );
}


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





//  --  FILTER: PROJECTS  --  //
$optListProjects	= '';
$inputSwitchProject	= '';
if( $useProjects && !empty( $userProjects ) ){

	if( $filterProjects === NULL )
		$filterProjects	= array_keys( $userProjects );
	
	$enabledProjects	= count( $filterProjects ) != count( $userProjects );
	$attributes			= array(
		'type'				=> 'checkbox',
		'name'				=> 'project',
		'id'				=> 'switch_project',
		'class'				=> 'optional-trigger',
		'onchange'			=> 'showOptionals(this);',
		'data-animation'	=> 'slide',
		'data-speed-show'	=> 500,
		'data-speed-hide'	=> 300,
		'checked'			=> $enabledProjects ? 'checked' : NULL,
	);
	$inputSwitchProject	= UI_HTML_Tag::create( 'input', NULL, $attributes );

	$list	= array();
	$index	= array();

	foreach( $userProjects as $project )
		$optProject[$project->projectId]	= $project->title;

	$optProject		= $helperFilter->renderFilterOptions( 'projects', $optProject, $filterProjects, 'filter-project', $missions, 'projectId' );
	if( count( $optProject ) > 1 ){
		$attributes	= array(
			'style'		=> 'margin: 0px; padding: 0px; list-style: none; display: '.( $enabledProjects ? 'block' : 'none' ),
			'class'		=> 'optional project project-true'
		);
		$optListProjects	= UI_HTML_Tag::create( 'ul', join( $optProject ), $attributes );
	}
}

$panelFilter	= '
<script>
$(document).ready(function(){
	WorkMissionFilter.__init();
	if(!parseInt($("#switch_view").val()))
		$("li.filter_status").show();
	if($("li.filter_project>ul").size())
		$("li.filter_project").show();
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
			<li>
				<label for="switch_view" style="">Sichtweise</label><br/>
				<select name="view" id="switch_view" onchange="WorkMissionFilter.changeView(this);" class="max">'.$optView.'</select>
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
			<li class="filter_status" style="display: none">
				<label for="switch_status" style="font-weight: bold">
					'.$inputSwitchStatus.'
					<span>Zustände</span>
				</label><br/>
				'.$optListStates.'
			</li>
			<li class="filter_project" style="display: none">
				<label for="switch_project" style="font-weight: bold">
					'.$inputSwitchProject.'
					<span>Projekte</span>
				</label><br/>
				'.$optListProjects.'
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
