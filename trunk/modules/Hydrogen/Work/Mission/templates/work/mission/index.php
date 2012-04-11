<?php

$w	= (object) $words['index'];

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

$buttonUp	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=ASC', $iconUp, 'tiny' );
$buttonDown	= UI_HTML_Elements::LinkButton( './work/mission/filter/?direction=DESC', $iconDown, 'tiny' );


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
	$buttonEdit		= UI_HTML_Elements::LinkButton( './work/mission/edit/'.$mission->missionId, $iconEdit, 'tiny' );
	$buttonRemove	= UI_HTML_Elements::LinkButton( './work/mission/setStatus/'.$mission->missionId.'/'.urlencode( '-3' ), $iconRemove, 'tiny' );
	$buttonLeft		= UI_HTML_Elements::LinkButton( './work/mission/changeDay/'.$mission->missionId.'/'.urlencode( '-1' ), $iconLeft, 'tiny' );
	$buttonRight	= UI_HTML_Elements::LinkButton( './work/mission/changeDay/'.$mission->missionId.'/'.urlencode( '+1' ), $iconRight, 'tiny' );
	
	if( !$days )
		$buttonLeft	= UI_HTML_Elements::LinkButton( './work/mission/changeDay/'.$mission->missionId.'/'.urlencode( '-1' ), $iconLeft, 'tiny', NULL, TRUE );
	
	$line		= '<td><div style="padding: 2px;">'.$graph.'</div></td><td>'.$link.'</td><td>'.$priority.'</td><td class="actions">'.$buttonEdit.' | '.$buttonLeft.$buttonRight.'</td>';
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

$colgroup	= UI_HTML_Elements::ColumnGroup( "13%", "60%", "14%", "13%" );
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
	UI_HTML_Tag::create( 'table', $colgroup.$tableHeads.join( $list[0] ) ),
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


$panelPort	= '
	<fieldset>
		<legend>Daten-Port</legend>
		'.UI_HTML_Elements::LinkButton( './work/mission/export', 'exportieren', 'button export' ).'
		<hr/>
		<form action="./work/mission/import" method="post" enctype="multipart/form-data">
			<input type="file" name="serial"/>'.UI_HTML_Elements::Button( 'import', 'importieren', 'button import' ).'
		</form>
	</fieldset>
';

$content	= '
<script>'.$script.'</script>
<style>
/*  table.sort.css  */
table tr th.sortable {
	cursor: pointer;
	text-decoration: underline;
	}
table tr th.sortable.ordered {
	}
table tr th.sortable.ordered.direction-asc {
	}
table tr th.sortable.ordered.direction-desc {
	}
	
</style>
<script>
function makeTableSortable(jq,options){
	var options = $.extend({order: null, direction: "ASC"},options);
	$("body").data("tablesort-options",options);
	jq.find("tr th div.sortable").each(function(){
		if($(this).data("column")){
			$(this).removeClass("sortable").parent().addClass("sortable");
			if($(this).data("column") == options.order){
				$(this).parent().addClass("ordered");
				$(this).parent().addClass("direction-"+options.direction.toLowerCase());
			}
			$(this).bind("click",function(){
				var head = $(this);
				var options = $("body").data("tablesort-options");
				var column = head.data("column");
				var direction = options.direction;
				if( options.order == column )
					direction = direction == "ASC" ? "DESC" : "ASC";
				var url = "./work/mission/filter/?order="+column+"&direction="+direction;
				document.location.href = url;
			});
		}
	});
}


$(document).ready(function(){
	makeTableSortable($("#layout-content table"),{
		url: "./work/mission/filter/",
		order: "'.$filterOrder.'",
		direction: "'.$filterDirection.'",
	});
});
</script>
<h2>'.$w->heading.'</h2>
<div class="column-left-20">
	'.$panelFilter.'
	'.$panelPort.'
</div>
<div class="column-left-80" style="font-size: 0.85em">
	'.$panelList.'
</div>
<div class="column-clear"></div>';

return $content;
?>