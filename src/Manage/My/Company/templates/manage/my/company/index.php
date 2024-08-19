<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<string,array<string,string>> $words */
/** @var View_Company $view */
/** @var array<object> $companies */

$w	= (object) $words['index'];

if( $companies ){
	$timeHelper	= new View_Helper_TimePhraser( $env );
	$rows		= [];
	foreach( $companies as $company ){
		$link	= HtmlTag::create( 'a', $company->title, [
			'href' => './manage/my/company/edit/'.$company->companyId
		] );
		$createdAt	= $timeHelper->convert( $company->createdAt, TRUE );
		$modifiedAt	= $company->modifiedAt ? $timeHelper->convert( $company->modifiedAt ) : "-";
		$rows[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $createdAt ),
			HtmlTag::create( 'td', $modifiedAt ),
		) );
	}
	$heads	= [
	//	'<input type="checkbox" class="toggler"/>',
		$words['index']['headTitle'],
		$words['index']['headCreatedAt'],
		$words['index']['headModifiedAt'],
	//	$words['index']['headAction'],
	];
	$heads		= HtmlElements::TableHeads( $heads );
	$colgroup	= HtmlElements::ColumnGroup( '57%', '15%', '15%' );
	$thead		= HtmlTag::create( 'thead', $heads );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table'] );
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
