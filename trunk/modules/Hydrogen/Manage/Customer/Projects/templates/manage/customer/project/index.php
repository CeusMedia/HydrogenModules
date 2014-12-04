<?php

#print_m( $projects );
#die;

$tabs		= View_Manage_Customer::renderTabs( $env, $customerId, 'project/'.$customerId );

$table		= '';
if( $relations ){
	$rows	= array();
	foreach( $relations as $relation ){
//print_m( $relation );
//die;
		$url	= './manage/project/'.$relation->projectId;
		$link	= UI_HTML_Tag::create( 'a', $relation->project->title, array( 'href' => $url ) );
		$urlRemove		= './manage/customer/project/remove/'.$relation->customerId.'/'.$relation->projectId;
		$iconRemove		= '<i class="icon-remove icon-white"></i>';
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'class'		=> 'btn btn-small btn-danger',
			'href'		=> $urlRemove,
			'title'		=> 'entfernen',
		) );
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $buttonRemove )
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( "100%" );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Titel'
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
	$table	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

$buttonAdd	= UI_HTML_Tag::create( 'a', '<i class="icon-plus icon-white"></i> hinzufügen', array( 'href' => './manage/customer/project/add', 'class' => 'btn btn-success' ) );


/*
$list	= array();
foreach( $projects as $project ){
	
	$list[]	= UI_HTML_Tag::create( 'li', $project->title, array( 'class' => 'autocut' ) );
}
$list	= UI_HTML_Tag::create( 'ul', $list );
*/

$optProject	= array( '' => '' );
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject	= UI_HTML_Elements::Options( $optProject );

$optType	= UI_HTML_Elements::Options( $words['types'] );

return '
<h3><span class="muted">Kunde</span> '.$customer->title.'</h3>
'.$tabs.'
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel">
			<div class="content-panel-inner">
				<h4>...</h4>
				'.$table.'
			</div>
		</div>
	</div>
	<div class="span4">
		<div class="content-panel">
			<div class="content-panel-inner">
				<h4>Zuweisen</h4>
				<form action="./manage/customer/project/add/'.$customerId.'" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_projectId">Projekt</label>
							<select id="input_projectId" name="projectId" onchange="this.form.submit()">'.$optProject.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_type">Art der Verbindung</label>
							<select id="input_type" name="type">'.$optType.'</select>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonAdd.'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
';
