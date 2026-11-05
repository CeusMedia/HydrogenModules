<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var object[] $reserves */
/** @var object[] $corporations */

$w		= (object) $env->getLanguage()->getSection( 'work/billing', 'reserve-index' );

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconCompany	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-building-o'] );

$list	= HtmlTag::create( 'em', $w->noEntries, ['class' => 'muted'] );

if( $reserves ){
	$list	= [];
	foreach( $reserves as $reserve ){
		$link	= HtmlTag::create( 'a', $reserve->title, ['href' => './work/billing/reserve/edit/'.$reserve->reserveId] );
		$corporation	= '<em class="muted">'.$w->personByShare.'</em>';
		if( $reserve->corporationId ){
			$corporation	= $corporations[$reserve->corporationId];
			$corporation	= HtmlTag::create( 'a', $iconCompany.'&nbsp;'.$corporation->title, [
				'href'	=> './work/billing/corporation/edit/'.$corporation->corporationId
			] );
		}
		$percent	= (float) $reserve->percent ? number_format( $reserve->percent, 2, ',', '.' ).'&nbsp;%' : '-';
		$amount		= (float) $reserve->amount ? number_format( $reserve->amount, 2, ',', '.' ).'&nbsp;&euro;' : '-';
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $corporation, ['class' => 'autocut'] ),
			HtmlTag::create( 'td', $reserve->personalize ? $w->yes_ : $w->no_, ['class' => 'cell-feature'] ),
			HtmlTag::create( 'td', $percent, ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', $amount, ['class' => 'cell-number'] ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( [
		'25%',
		'25%',
		'80',
		'100',
		'100',
	] );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', $w->headTitle ),
		HtmlTag::create( 'th', $w->headTarget ),
		HtmlTag::create( 'th', '<small>'.$w->headPersonalized.'</small>', ['class' => 'cell-feature'] ),
		HtmlTag::create( 'th', $w->headPercent, ['class' => 'cell-number'] ),
		HtmlTag::create( 'th', $w->headAmount, ['class' => 'cell-number'] ),
	] ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-fixed'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' '.$w->buttonAdd, [
	'href'	=> './work/billing/reserve/add',
	'class'	=> 'btn btn-success',
] );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				'.$list.'
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	</div>
</div>';
