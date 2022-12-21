<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$tabs		= View_Manage_Customer::renderTabs( $env, $customerId, 'project/'.$customerId );

$table		= '';
if( $relations ){
	$rows	= [];
	foreach( $relations as $relation ){
//print_m( $relation );
//die;
		$url	= './manage/project/'.$relation->projectId;
		$link	= HtmlTag::create( 'a', $relation->project->title, ['href' => $url] );
		$urlRemove		= './manage/customer/project/remove/'.$relation->customerId.'/'.$relation->projectId;
		$iconRemove		= '<i class="icon-remove icon-white"></i>';
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, array(
			'class'		=> 'btn btn-small btn-danger',
			'href'		=> $urlRemove,
			'title'		=> 'entfernen',
		) );
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $buttonRemove )
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( "100%" );
	$thead	= HtmlTag::create( 'thead', HtmlElements::TableHeads( array(
		'Titel'
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
}

$buttonAdd	= HtmlTag::create( 'a', '<i class="icon-plus icon-white"></i> hinzufügen', ['href' => './manage/customer/project/add', 'class' => 'btn btn-success'] );


/*
$list	= [];
foreach( $projects as $project ){
	
	$list[]	= HtmlTag::create( 'li', $project->title, ['class' => 'autocut'] );
}
$list	= HtmlTag::create( 'ul', $list );
*/

$optProject	= ['' => ''];
foreach( $projects as $project )
	$optProject[$project->projectId]	= $project->title;
$optProject	= HtmlElements::Options( $optProject );

$optType	= HtmlElements::Options( $words['types'] );

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
