<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View_Work_Newsletter_Template $view */
/** @var object $words */
/** @var object $template */
/** @var bool $isUsed */
/** @var string $buttons */

$w				= (object) $words->styles;

//print_m( $template->newsletterTemplateId );die;

//  --  PANEL: FORM  --  //
$value			= htmlentities( $template->style, ENT_QUOTES, 'UTF-8' );
$panelForm		= '
<div class="content-panel">
	<h3>Style-Angaben für HTML-Format <small class="muted"></small></h3>
	<div class="content-panel-inner">
<!--		<label for="input_style">'.$words->edit->labelStyle.'</label>-->
		'.HtmlTag::create( 'textarea', $value, [
			'name'		=> 'style',
			'id'		=> 'input_style',
			'class'		=> 'span12 CodeMirror-auto',
			'rows'		=> 30,
			'readonly'	=> $isUsed ? "readonly" : NULL,
//			'disabled'	=> $isUsed ? "disabled" : NULL,
		] ).'
		'.$buttons.'
	</div>
</div>';

extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/work/newsletter/template/style/' ) );

return $textTop.HtmlTag::create( 'div', [
	HtmlTag::create( 'div', $panelForm, ['class' => 'span6'] ),
	HtmlTag::create( 'div', [
		$textInfo,
		$view->renderHtmlPreviewPanel( $template )
	], ['class' => 'span6'] )
], ['class' => 'row-fluid'] ).$textBottom;
