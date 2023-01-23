<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object[] $corporations */

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconPerson		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-user-o'] );
$iconCompany	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-building-o'] );

$list	= HtmlTag::create( 'div', HtmlTag::create( 'em', 'Keine gefunden.', ['class' => 'muted'] ), ['class' => 'alert alert-info'] );

if( $corporations ){
	$list	= [];
	foreach( $corporations as $corporation ){
		$link	= HtmlTag::create( 'a', $iconCompany.'&nbsp;'.$corporation->title, [
			'href' => './work/billing/corporation/edit/'.$corporation->corporationId
		] );
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link, ['class' => 'autocut'] ),
			HtmlTag::create( 'td', number_format( $corporation->balance, 2, ',', '.' ).'&nbsp;&euro;', ['class' => 'cell-number'] ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( [
		'',
		'100',
	] );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Bezeichnung' ),
		HtmlTag::create( 'th', 'Balance', ['class' => 'cell-number'] )
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-fixed'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' neues Unternehmen', [
	'href'	=> './work/billing/corporation/add',
	'class'	=> 'btn btn-success',
] );

return '
<div class="content-panel">
	<h3>Unternehmen</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';
