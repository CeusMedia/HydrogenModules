<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconCompany	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-building-o'] );

$list	= HtmlTag::create( 'em', 'Keine gefunden.', ['class' => 'muted'] );

if( $reserves ){
	$list	= [];
	foreach( $reserves as $reserve ){
		$link	= HtmlTag::create( 'a', $reserve->title, ['href' => './work/billing/reserve/edit/'.$reserve->reserveId] );
		$corporation	= '<em class="muted">Person per Anteil</em>';
		if( $reserve->corporationId ){
			$corporation	= $corporations[$reserve->corporationId];
			$corporation	= HtmlTag::create( 'a', $iconCompany.'&nbsp;'.$corporation->title, array(
				'href'	=> './work/billing/corporation/edit/'.$corporation->corporationId
			) );
		}
		$percent	= (float) $reserve->percent ? number_format( $reserve->percent, 2, ',', '.' ).'&nbsp;%' : '-';
		$amount		= (float) $reserve->amount ? number_format( $reserve->amount, 2, ',', '.' ).'&nbsp;&euro;' : '-';
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $corporation, ['class' => 'autocut'] ),
			HtmlTag::create( 'td', $reserve->personalize ? 'ja' : 'nein', ['class' => 'cell-feature'] ),
			HtmlTag::create( 'td', $percent, ['class' => 'cell-number'] ),
			HtmlTag::create( 'td', $amount, ['class' => 'cell-number'] ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( array(
		'25%',
		'25%',
		'80',
		'100',
		'100',
	) );
	$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
		HtmlTag::create( 'th', 'Bezeichnung' ),
		HtmlTag::create( 'th', 'Zielkonto' ),
		HtmlTag::create( 'th', '<small>personalisiert</small>', ['class' => 'cell-feature'] ),
		HtmlTag::create( 'th', 'Prozent', ['class' => 'cell-number'] ),
		HtmlTag::create( 'th', 'Betrag', ['class' => 'cell-number'] ),
	) ) );
	$tbody	= HtmlTag::create( 'tbody', $list );
	$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-fixed'] );
}

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' neue Rücklage', array(
	'href'	=> './work/billing/reserve/add',
	'class'	=> 'btn btn-success',
) );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>Rücklagen</h3>
			<div class="content-panel-inner">
				'.$list.'
				<div class="buttonbar">
					'.$buttonAdd.'
				</div>
			</div>
		</div>
	</div>
</div>';
