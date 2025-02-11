<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_Work_Newsletter_Template $view */
/** @var object $words */
/** @var object $template */
/** @var string $templateId */
/** @var bool $isUsed */
/** @var array<string> $styles */

//  --  PANEL: SOURCE LIST  --  //
$w				= (object) $words->styles;
$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
$labelEmpty		= HtmlTag::create( 'em', $w->empty, ['class' => 'muted'] );

$buttonAdd		= HtmlTag::create( 'button', $iconAdd.$w->buttonAdd, [
	'type'			=> "button",
	'class'			=> "btn btn-success btn-small",
	'data-toggle'	=> "modal",
	'data-target'	=> "#modal-style-add",
] );
if( $isUsed )
	$buttonAdd		= HtmlTag::create( 'button', $iconAdd.$w->buttonAdd, [
		'type'			=> "button",
		'class'			=> "btn btn-success btn-small",
		'disabled'		=> 'disabled',
	] );

$listStyles		= HtmlTag::create( 'div', $labelEmpty, ['class' => 'alert alert-info'] );
if( [] !== $styles ){
	foreach( $styles as $nr => $item ){
		$label	= preg_replace( "@^([a-z]+://[^/]+/)@", '<small class="muted">\\1</small><br/>', $item );
		$attributes		= [
			'href'		=> './work/newsletter/template/removeStyle/'.$templateId.'/'.$nr,
			'class'		=> 'btn btn-mini btn-inverse'
		];
		$linkRemove		= HtmlTag::create( 'a', $iconRemove.$w->buttonRemove, $attributes );
		if( $isUsed )
			$linkRemove	= HtmlTag::create( 'button', $iconRemove.$w->buttonRemove, array_merge( $attributes, ['disabled' => 'disabled'] ) );
		$linkRemove		= HtmlTag::create( 'div', $linkRemove, ['class' => 'pull-right'] );
		$styles[$nr]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $label, ['class' => ''] ),
			HtmlTag::create( 'td', $linkRemove, ['class' => ''] ),
		] );
	}
	$colgroup		= HtmlElements::ColumnGroup( '', '120px' );
	$tableHeads		= HtmlElements::TableHeads( ['Einträge', ''] );
	$thead			= HtmlTag::create( 'thead', $tableHeads );
	$tbody			= HtmlTag::create( 'tbody', $styles );
	$listStyles		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, [
		'class'	=> "table table-condensed table-striped table-fixed table-striped"
	] );
}

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/work/newsletter/template/styles/' ) );

return $textTop.HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		HtmlTag::create( 'div', [
			HtmlTag::create( 'h3', $w->heading ),
			HtmlTag::create( 'div', [
				$listStyles,
				HtmlTag::create( 'div', $buttonAdd, ['class' => 'buttonbar'] )
			], ['class' => 'content-panel-inner'] ),
		], ['class' => 'content-panel'] )
	], ['class' => 'span6'] ),
	HtmlTag::create( 'div', [
		$textInfo,
		$view->renderHtmlPreviewPanel( $template )
	], ['class' => 'span6'] ),
], ['class' => 'row-fluid'] ).$textBottom;
