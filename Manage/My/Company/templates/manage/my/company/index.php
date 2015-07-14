<?php
$w	= (object) $words['index'];

if( $companies ){
	$timeHelper	= new View_Helper_TimePhraser( $env );
	$rows		= array();
	foreach( $companies as $company ){
		$link	= UI_HTML_Tag::create( 'a', $company->title, array(
			'href' => './manage/my/company/edit/'.$company->companyId
		) );
		$createdAt	= $timeHelper->convert( $company->createdAt, TRUE );
		$modifiedAt	= $company->modifiedAt ? $timeHelper->convert( $company->modifiedAt ) : "-";
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $createdAt ),
			UI_HTML_Tag::create( 'td', $modifiedAt ),
		) );
	}
	$heads	= array(
	//	'<input type="checkbox" class="toggler"/>',
		$words['index']['headTitle'],
		$words['index']['headCreatedAt'],
		$words['index']['headModifiedAt'],
	//	$words['index']['headAction'],
	);
	$heads		= UI_HTML_Elements::TableHeads( $heads );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '57%', '15%', '15%' );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table' ) );
}

return '
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel">
			<h3>'.$w->legend.'</h3>
			<div class="content-panel-inner">
				'.$list.'
			</div>
		</div>
	</div>
</div>';
?>
