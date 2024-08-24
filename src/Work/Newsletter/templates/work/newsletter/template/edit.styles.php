<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
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
$listStyles		= HtmlTag::create( 'div', $labelEmpty, ['class' => 'alert alert-info'] );
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
if( $styles ){
	foreach( $styles as $nr => $item ){
		$label	= preg_replace( "@^([a-z]+://[^/]+/)@", '<small class="muted">\\1</small><br/>', $item );
		$attributes		= [
			'href'		=> './work/newsletter/template/removeStyle/'.$templateId.'/'.$nr,
			'class'		=> 'btn btn-mini btn-inverse'
		];
		$linkRemove			= HtmlTag::create( 'a', $iconRemove.$w->buttonRemove, $attributes );
		if( $isUsed )
			$linkRemove		= HtmlTag::create( 'button', $iconRemove.$w->buttonRemove, array_merge( $attributes, ['disabled' => 'disabled'] ) );
		$linkRemove			= HtmlTag::create( 'div', $linkRemove, ['class' => 'pull-right'] );
		$styles[$nr]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $label, ['class' => ''] ),
			HtmlTag::create( 'td', $linkRemove, ['class' => ''] ),
		] );
	}
	$colgroup		= HtmlElements::ColumnGroup( "", "120px" );
	$tableHeads		= HtmlElements::TableHeads( ['Einträge', ''] );
	$thead			= HtmlTag::create( 'thead', $tableHeads );
	$tbody			= HtmlTag::create( 'tbody', $styles );
	$listStyles		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, [
		'class'	=> "table table-condensed table-striped table-fixed table-striped"
	] );
}
$panelList	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$listStyles.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';

$urlPreview			= './work/newsletter/template/preview/html/'.$template->newsletterTemplateId;
$iframeHtml			= HtmlTag::create( 'iframe', '', [
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
] );
$buttonPreviewHtml	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-eye"></i>&nbsp;Vorschau', [
	'type'			=> 'button',
	'class'			=> 'btn btn-info',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
] );
$panelPreview	= '
<div class="content-panel">
	<h4>
		<span>HTML-Vorschau</span>
		<div style="float: right">
			'.$buttonPreviewHtml.'
		</div>
	</h4>
	<div class="content-panel-inner">
		<div id="newsletter-preview">
			<div id="newsletter-preview-container">
		 		<div id="newsletter-preview-iframe-container">
					'.$iframeHtml.'
				</div>
			</div>
		</div>
	</div>
</div>';

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/work/newsletter/template/styles/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span6">
		'.$panelList.'
	</div>
	<div class="span6">
		'.$textInfo.'
		'.$panelPreview.'
	</div>
</div>'.$textBottom;
