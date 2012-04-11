<?php

$w	= (object) $words['index'];

//  --  FILTER  --  //
$optOrder	= array( '' => '' ) + $words['filter-orders'];
$optOrder	= UI_HTML_Elements::Options( $optOrder, $session->get( 'filter_mission_order' ) );

$optDirection	= array( '' => '' ) + $words['filter-directions'];
$optDirection	= UI_HTML_Elements::Options( $optDirection, $session->get( 'filter_mission_direction' ) );

$iconUp		= UI_HTML_Elements::Image( 'http://icons.ceusmedia.de/famfamfam/silk/arrow_up.png', $words['filter-directions']['ASC'] );
$iconDown	= UI_HTML_Elements::Image( 'http://icons.ceusmedia.de/famfamfam/silk/arrow_down.png', $words['filter-directions']['DESC'] );

$buttonUp	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=ASC', $iconUp );
$buttonDown	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=DESC', $iconDown );


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
</form>
';


//  --  LIST  --  //
$list	= array(
	0 => array(),
	1 => array(),
	2 => array(),
	3 => array(),
	4 => array(),
	5 => array(),
	6 => array(),
	7 => array(),
);	
$indicator	= new UI_HTML_Indicator();
$disabled	= array();
$today		= strtotime( date( 'Y-m-d', time() ) );
foreach( $missions as $mission ){
	$link		= UI_HTML_Elements::Link( './work/mission/edit/'.$mission->missionId, $mission->content );
	$diff		= strtotime( $mission->day ) - $today;
	$days		= $diff / ( 24 * 60 * 60);
	$days		= max( min( $days , 6 ), 0 );
	$graph		= $indicator->build( $mission->status, 6 );
	$priority	= $words['priorities'][$mission->priority];
	$class		= 'row-priority priority-'.$mission->priority;
	$line		= '<td><div style="padding: 2px;">'.$graph.'</div></td><td>'.$link.'</td><td>'.$priority.'</td><td></td>';
	$list[$days][]	= UI_HTML_Tag::create( 'tr', $line, array( 'class' => $class ) );
}

for( $i=0; $i<=7; $i++ )
	if( !count( $list[$i] ) )
		$disabled[]	= $i;

function getFutureDate( $daysInFuture = 0 ){
	return date( "j.n.", time() + $daysInFuture * 24 * 60 * 60 );
}
function getCount( $list, $days ){
	$count	= count( $list[$days] );
	if( $count )
		return ' <small>('.$count.')</small>';
}
function renderTable(){}

$colgroup	= UI_HTML_Elements::ColumnGroup( "13%", "60%", "15%", "12%" );
$tableHeads	= UI_HTML_Elements::TableHeads( array(
	UI_HTML_Tag::create( 'div', 'Progress', array( 'class' => 'sortable', 'data-column' => 'status' ) ),
	UI_HTML_Tag::create( 'div', 'Title', array( 'class' => 'sortable', 'data-column' => 'content' ) ),
	UI_HTML_Tag::create( 'div', 'Priority', array( 'class' => 'sortable', 'data-column' => 'priority' ) ),
	UI_HTML_Tag::create( 'div', 'Actions', array( 'class' => 'sortable', 'data-column' => NULL ) )
) );

UI_HTML_Tabs::$version	= 3;
$tabs	= new UI_HTML_Tabs();
$tabs->addTab(
	'Heute, '.getFutureDate( 0 ).getCount( $list, 0 ),
	UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[0] ), array( 'class' => 'tablesorter' ) ),
	'tab-days-0'
);
$tabs->addTab(
	'Morgen, '.getFutureDate( 1 ).getCount( $list, 1 ),
	UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[1] ) ),
	'tab-days-1'
);
$tabs->addTab(
	'Übermorgen, '.getFutureDate( 2 ).getCount( $list, 2 ),
	UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[2] ) ),
	'tab-days-2'
);
$tabs->addTab(
	getFutureDate( 3 ).getCount( $list, 3 ),
	UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[3] ) ),
	'tab-days-3'
);
$tabs->addTab(
	getFutureDate( 4 ).getCount( $list, 4 ),
	UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[4] ) ),
	'tab-days-4'
);
$tabs->addTab(
	getFutureDate( 5 ).getCount( $list, 5 ),
	UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[5] ) ),
	'tab-days-5'
);
/*$tabs->addTab(
	getFutureDate( 6 ).getCount( $list, 6 ),
	UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[6] ) ),
	'tab-days-6'
);*/
$tabs->addTab(
	'Zukunft'.getCount( $list, 6 ),
	UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[6] ) ),
	'tab-days-7'
);
$script	= $tabs->buildScript( '#tabs-missions', array( 'disabled' => $disabled ) );
$tabs	= $tabs->buildTabs( 'tabs-missions' );

$buttonAdd	= UI_HTML_Elements::LinkButton( './work/mission/add', $w->buttonAdd, 'button add' );
	
$panelList	= '
'.$tabs.'
'.$buttonAdd;

/*
<script>
$(document).ready(function(){
	$("table").tablesorter();
});
</script>
<script src="http://js.ceusmedia.de/jquery/tablesorter/2.0.5/jquery.tablesorter.min.js"></script>
<link rel="stylesheet" href="http://js.ceusmedia.de/jquery/tablesorter/2.0.5/themes/blue/style.css"/>
 */

$content	= '
<script>'.$script.'</script>
	

<script>
$(document).ready(function(){
	$("th.sortable").bind(function(){});
});
</script>



<h2>'.$w->heading.'</h2>
<div class="column-left-20">
	'.$panelFilter.'
</div>
<div class="column-left-80" style="font-size: 0.85em">
	'.$panelList.'
</div>
<div class="column-clear"></div>';

return $content;
?>