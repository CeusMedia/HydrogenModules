<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View_Work_Newsletter_Template $view */
/** @var object $words */
/** @var object $template */

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/work/newsletter/template/text' ) );
extract( $view->populateTexts( ['placeholders'], 'html/work/newsletter/template/' ) );

//  --  PANEL: PREVIEW  --  //

$textarea		= HtmlTag::create( 'textarea', $template->plain, [
	'name'		=> 'plain',
	'id'		=> 'input_plain',
	'class'		=> 'span12 CodeMirror-auto',
	'rows'		=> 30,
	'readonly'	=> $isUsed ? "readonly" : NULL,
//	'disabled'	=> $isUsed ? "disabled" : NULL,
] );

$content	= $textTop.'
<div class="row-fluid">
	<div class="span7">
		<div class="content-panel">
			<h3>
				'.$words->edit->labelPlain.'
				<div class="pull-right">
					<a href="#modal-newsletter-template-placeholders" role="button" class="btn btn-mini not-btn-info" data-toggle="modal"><i class="fa fa-fw fa-info-circle"></i>&nbsp;Hilfe</a>
				</div>
			</h3>
			<div class="content-panel-inner">
				'.$textarea.'
				'.$buttons.'
			</div>
		</div>
	</div>
	<div class="span5">
		'.$textInfo.'
		'.$view->renderTextPreviewPanel( $template ).'
	</div>
</div>'.$textBottom.$textPlaceholders;

return $content;
