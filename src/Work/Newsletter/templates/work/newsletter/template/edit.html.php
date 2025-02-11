<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View_Work_Newsletter_Template $view */
/** @var object $words */
/** @var bool $tabbedLinks */
/** @var object $template */
/** @var bool $isUsed */

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/work/newsletter/template/html' ) );
extract( $view->populateTexts( ['placeholders'], 'html/work/newsletter/template/' ) );

$value		= htmlentities( $template->html, ENT_QUOTES, 'UTF-8' );
$content	= $textTop.'
<div class="row-fluid">
	<div class="span7">
		<div class="content-panel">
			<h3>'.$words->edit->labelHtml.'
				<div class="pull-right">
					<a href="#modal-newsletter-template-placeholders" role="button" class="btn btn-mini not-btn-info" data-toggle="modal"><i class="fa fa-fw fa-info-circle"></i>&nbsp;Hilfe</a>
				</div>
			</h3>
			<div class="content-panel-inner">
				'.HtmlTag::create( 'textarea', $value, [
					'name'		=> 'html',
					'id'		=> 'input_html',
					'class'		=> 'CodeMirror-auto',
					'rows'		=> 30,
					'readonly'	=> $isUsed ? "readonly" : NULL,
//					'disabled'	=> $isUsed ? "disabled" : NULL,
				] ).'
				'.$buttons.'
			</div>
		</div>
	</div>
	<div class="span5">
		'.$view->renderHtmlPreviewPanel( $template ).'
	</div>
<!--	<div class="span3">
		'.$textInfo.'
	</div>-->
</div>
'.$textBottom.'
'.$textPlaceholders;

return $content;
