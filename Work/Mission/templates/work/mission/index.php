<?php

$w	= (object) $words['index'];

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
foreach( $missions as $mission ){
	$link	= UI_HTML_Elements::Link( './work/mission/edit/'.$mission->missionId, $mission->content );
	$days	= min( $mission->daysLeft, 7 );
	$graph	= $indicator->build( $mission->status, 6 );
	$list[$days][]	= UI_HTML_Tag::create( 'li', $graph.'&nbsp;'.$link );
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

UI_HTML_Tabs::$version	= 3;
$tabs	= new UI_HTML_Tabs();
$tabs->addTab(
	'Heute, '.getFutureDate( 0 ).getCount( $list, 0 ),
	UI_HTML_Tag::create( 'ul', join( $list[0] ) ),
	'tab-days-0'
);
$tabs->addTab(
	'Morgen, '.getFutureDate( 1 ).getCount( $list, 1 ),
	UI_HTML_Tag::create( 'ul', join( $list[1] ) ),
	'tab-days-1'
);
$tabs->addTab(
	'Übermorgen, '.getFutureDate( 2 ).getCount( $list, 2 ),
	UI_HTML_Tag::create( 'ul', join( $list[2] ) ),
	'tab-days-2'
);
$tabs->addTab(
	getFutureDate( 3 ).getCount( $list, 3 ),
	UI_HTML_Tag::create( 'ul', join( $list[3] ) ),
	'tab-days-3'
);
$tabs->addTab(
	getFutureDate( 4 ).getCount( $list, 4 ),
	UI_HTML_Tag::create( 'ul', join( $list[4] ) ),
	'tab-days-4'
);
$tabs->addTab(
	getFutureDate( 5 ).getCount( $list, 5 ),
	UI_HTML_Tag::create( 'ul', join( $list[5] ) ),
	'tab-days-5'
);
$tabs->addTab(
	getFutureDate( 6 ).getCount( $list, 6 ),
	UI_HTML_Tag::create( 'ul', join( $list[6] ) ),
	'tab-days-6'
);
$tabs->addTab(
	'Zukunft'.getCount( $list, 7 ),
	UI_HTML_Tag::create( 'ul', join( $list[7] ) ),
	'tab-days-7'
);
$script	= $tabs->buildScript( '#tabs-missions', array( 'disabled' => $disabled ) );
$tabs	= $tabs->buildTabs( 'tabs-missions' );

$buttonAdd	= UI_HTML_Elements::LinkButton( './work/mission/add', $w->buttonAdd, 'button add' );

	
$panelList	= '
'.$tabs.'
'.$buttonAdd;

$panelFilter	= '
<form action="./work/mission/filter" method="post">
	<fieldset>
		<legend>Filter</legend>
		<ul class="input">
			<li>
				<label for="input_query"><strike>'.$w->labelQuery.'</strike></label><br/>
				<input name="query" id="input_query" value="'.$request->get( 'query' ).'" disabled="disabled"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'filter', $w->buttonFilter, 'button filter' ).'
			'.UI_HTML_Elements::LinkButton( './work/mission/filter?reset', $w->buttonReset, 'button reset' ).'
		</div>
	</fieldset>
</form>
';


$content	= '
<script>'.$script.'</script>
<h2>'.$w->heading.'</h2>
<div class="column-left-25">
	'.$panelFilter.'
</div>
<div class="column-left-75">
	'.$panelList.'
</div>
<div class="column-clear"></div>';

return $content;
?>