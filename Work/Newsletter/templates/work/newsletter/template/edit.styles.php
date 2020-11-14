<?php

//  --  PANEL: SOURCE LIST  --  //
$w				= (object) $words->styles;
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) ).'&nbsp;';
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'&nbsp;';
$labelEmpty		= UI_HTML_Tag::create( 'em', $w->empty, array( 'class' => 'muted' ) );
$listStyles		= UI_HTML_Tag::create( 'div', $labelEmpty, array( 'class' => 'alert alert-info' ) );
$buttonAdd		= UI_HTML_Tag::create( 'button', $iconAdd.$w->buttonAdd, array(
	'type'			=> "button",
	'class'			=> "btn btn-success btn-small",
	'data-toggle'	=> "modal",
	'data-target'	=> "#modal-style-add",
) );
if( $isUsed )
	$buttonAdd		= UI_HTML_Tag::create( 'button', $iconAdd.$w->buttonAdd, array(
		'type'			=> "button",
		'class'			=> "btn btn-success btn-small",
		'disabled'		=> 'disabled',

	) );
if( $styles ){
	foreach( $styles as $nr => $item ){
		$label	= preg_replace( "@^([a-z]+://[^/]+/)@", '<small class="muted">\\1</small><br/>', $item );
		$attributes		= array(
			'href'		=> './work/newsletter/template/removeStyle/'.$templateId.'/'.$nr,
			'class'		=> 'btn btn-mini btn-inverse'
		);
		$linkRemove			= UI_HTML_Tag::create( 'a', $iconRemove.$w->buttonRemove, $attributes );
		if( $isUsed )
			$linkRemove		= UI_HTML_Tag::create( 'button', $iconRemove.$w->buttonRemove, array_merge( $attributes, array( 'disabled' => 'disabled' ) ) );
		$linkRemove			= UI_HTML_Tag::create( 'div', $linkRemove, array( 'class' => 'pull-right' ) );
		$styles[$nr]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $label, array( 'class' => '' ) ),
			UI_HTML_Tag::create( 'td', $linkRemove, array( 'class' => '' ) ),
		) );
	}
	$colgroup		= UI_HTML_Elements::ColumnGroup( "", "120px" );
	$tableHeads		= UI_HTML_Elements::TableHeads( array( 'Einträge', '' ) );
	$thead			= UI_HTML_Tag::create( 'thead', $tableHeads );
	$tbody			= UI_HTML_Tag::create( 'tbody', $styles );
	$listStyles		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
		'class'	=> "table table-condensed table-striped table-fixed table-striped"
	) );
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
$iframeHtml			= UI_HTML_Tag::create( 'iframe', '', array(
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
) );
$buttonPreviewHtml	= UI_HTML_Tag::create( 'button', '<i class="fa fa-fw fa-eye"></i>&nbsp;Vorschau', array(
	'type'			=> 'button',
	'class'			=> 'btn btn-info',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
) );
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

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/work/newsletter/template/styles/' ) );

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
?>
