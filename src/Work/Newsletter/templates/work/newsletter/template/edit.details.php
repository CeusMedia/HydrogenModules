<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var bool $tabbedLinks */
/** @var object $template */
/** @var array $newsletters */
/** @var string $templateId */
/** @var bool $isUsed */
/** @var array $buttons */

$w		= $words->edit;

$iconCopy		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-clone'] ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
$iconPreview	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] ).'&nbsp;';
$optStatus		= HtmlElements::Options( $words->states, $template->status );

$listNewsletters	= '<em><small class="muted">Nicht verwendet.</small></em>';
if( $newsletters ){
	$listNewsletters	= [];
	foreach( $newsletters as $newsletter ){
		$link	= HtmlTag::create( 'a', $newsletter->title, ['href' => './work/newsletter/edit/'.$newsletter->newsletterId] );
		$listNewsletters[]	= HtmlTag::create( 'li', $link );
	}
	$listNewsletters	= HtmlTag::create( 'ul', $listNewsletters, ['class' => 'unstyled nav nav-pills nav-stacked'] );
}

/*  --  PANEL: PREVIEW: HTML  --  */
$urlPreview		= './work/newsletter/template/preview/html/'.$template->newsletterTemplateId;
$iframeHtml		= HtmlTag::create( 'iframe', '', [
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
] );
$buttonPreviewHtml	= HtmlTag::create( 'button', $iconPreview.'Vorschau', [
	'type'			=> 'button',
	'class'			=> 'btn btn-info btn-mini',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
] );
$panelPreviewHtml	= '
<div id="newsletter-preview" class="half-size">
	<div id="newsletter-preview-container">
 		<div id="newsletter-preview-iframe-container">
			'.$iframeHtml.'
		</div>
	</div>
</div>';

/*  --  PANEL: PREVIEW: TEXT  --  */
$urlPreview		= './work/newsletter/template/preview/text/'.$template->newsletterTemplateId;
$iframeText		= HtmlTag::create( 'iframe', '', [
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
] );
$buttonPreviewText	= HtmlTag::create( 'button', $iconPreview.'Vorschau', [
	'type'			=> 'button',
	'class'			=> 'btn btn-info btn-mini',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
] );
$panelPreviewText	= '
<div id="newsletter-preview" class="half-size">
	<div id="newsletter-preview-container">
 		<div id="newsletter-preview-iframe-container">
			'.$iframeText.'
		</div>
	</div>
</div>';

$panelPreview		= '
<div class="content-panel">
	<h4>
		<span>HTML-Vorschau</span>
		<div style="float: right">
			'.$buttonPreviewHtml.'
		</div>
	</h4>
	<div class="content-panel-inner">
		'.$panelPreviewHtml.'
	</div>
</div>
<div class="content-panel">
	<h4>
		<span>Text-Vorschau</span>
		<div style="float: right">
			'.$buttonPreviewText.'
		</div>
	</h4>
	<div class="content-panel-inner">
		'.$panelPreviewText.'
	</div>
</div>';

/*  --  PANEL: NEWSLETTERS  --  */
$panelNewsletters	= '';
if( isset( $newsletters ) && count( $newsletters ) )
	$panelNewsletters	= '
<div class="content-panel">
	<h3>Verwendung</h3>
	<div class="content-panel-inner">
		<div style="max-height: 400px; overflow-x: hidden; overflow-y: auto;">
			'.$listNewsletters.'
		</div>
	</div>
</div>';


/*  --  PANEL: COPY  --  */
$buttonCopy		= HtmlTag::create( 'a', $iconCopy.$words->edit->buttonCopy, [
	'class'		=> "btn btn-success",
	'href'		=> "./work/newsletter/template/add?templateId=".$templateId
] );
$panelCopy		= '
<div class="content-panel">
	<h3>Vorlage kopieren</h3>
	<div class="content-panel-inner">
		<div data-class="alert alert-info">
			Eine neue Vorlage erstellen und dabei diese Vorlage als Vorgabe verwenden.<br/>
			Dabei werden folgende Angaben übernommen:
			<ul>
				<li>HTML-Gerüst</li>
				<li>Text-Gerüst</li>
				<li>Style-Angaben</li>
				<li>Style-Dateiverweise</li>
				<li>Absender-Angaben</li>
				<li>Impressum</li>
			</ul>
		</div>
		<div class="buttonbar">
			'.$buttonCopy.'
		</div>
	</div>
</div>';

/*  --  PANEL: REMOVE  --  */
$buttonRemove	= HtmlTag::create( 'a', $iconRemove.$words->edit->buttonRemove, [
	'class'		=> "btn btn-danger",
	'href'		=> $isUsed ? '#' : "./work/newsletter/template/remove/".$templateId,
	'disabled'	=> $isUsed ? 'disabled' : NULL,
	'onclick'	=> $isUsed ? "alert('".$words->edit->buttonRemoveDisabled."'); return false;" : NULL,
] );

$panelRemove	= '';
if( isset( $newsletters ) && !count( $newsletters ) )
	$panelRemove	= '
<div class="content-panel">
	<h3>Vorlage entfernen</h3>
	<div class="content-panel-inner">
		<p>
			Diese Vorlage wurde noch nicht verwendet und kann daher entfernt werden, wenn sie nicht mehr benötigt wird.
		</p>
		<div class="alert alert-info">
			Sobald eine Kampagne mit dieser Vorlage versendet wird, kann die Vorlage nicht mehr entfernt werden.
		</div>
		<div class="buttonbar">
			'.$buttonRemove.'
		</div>
	</div>
</div>';

$panelEdit		= '
<div class="content-panel">
	<h3>'.$words->edit->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span8">
				<div class="row-fluid">
					<div class="span12">
						<label for="input_title" class="mandatory">'.$words->edit->labelTitle.'</label>
<!--						<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ).'"/>-->
						'.HtmlTag::create( 'input', NULL, [
							'type'		=> 'text',
							'name'		=> 'title',
							'id'		=> 'input_title',
							'class'		=> 'span12',
							'value'		=> $template->title,
							'required'	=> 'required',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
						] ).'
					</div>
				</div>
<!--				<div class="row-fluid">
					<div class="span4">
						<label for="input_status">'.$words->edit->labelStatus.'</label>
						'.HtmlTag::create( 'select', $optStatus, [
							'name'		=> 'status',
							'id'		=> 'input_status',
							'class'		=> 'span12',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						] ).'
					</div>
				</div>-->
				<div class="row-fluid">
					<div class="span6">
						<label for="input_senderAddress" class="mandatory">'.$words->edit->labelSenderAddress.'</label>
						'.HtmlTag::create( 'input', NULL, [
							'type'		=> "text",
							'name'		=> "senderAddress",
							'id'		=> "input_senderAddress",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->senderAddress ?? '', ENT_QUOTES, 'UTF-8' ),
							'required'	=> 'required',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						] ).'
					</div>
					<div class="span6">
						<label for="input_senderName" class="mandatory">'.$words->edit->labelSenderName.'</label>
						'.HtmlTag::create( 'input', NULL, [
							'type'		=> "text",
							'name'		=> "senderName",
							'id'		=> "input_senderName",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->senderName ?? '', ENT_QUOTES, 'UTF-8' ),
							'required'	=> 'required',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						] ).'
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<label for="input_status" class="checkbox">
						'.HtmlTag::create( 'input', NULL, [
							'type'		=> 'checkbox',
							'name'		=> 'status',
							'value'		=> Model_Newsletter_Template::STATUS_READY,
							'id'		=> 'input_status',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
							'checked'	=> $template->status >= Model_Newsletter_Template::STATUS_READY ? 'checked' : NULL,
						] ).'
							'.$words->edit->labelReady.'
						</label>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<label for="input_authorName">'.$w->labelAuthorName.'</label>
						'.HtmlTag::create( 'input', NULL, [
							'type'		=> "text",
							'name'		=> "authorName",
							'id'		=> "input_authorName",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->authorName ?? '', ENT_QUOTES, 'UTF-8' ),
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						] ).'
					</div>
					<div class="span6">
						<label for="input_authorName">'.$w->labelAuthorEmail ?? ''.'</label>
						'.HtmlTag::create( 'input', NULL, [
							'type'		=> "text",
							'name'		=> "authorEmail",
							'id'		=> "input_authorEmail",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->authorEmail ?? '', ENT_QUOTES, 'UTF-8' ),
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						] ).'
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<label for="input_authorName">'.$w->labelAuthorCompany ?? ''.'</label>
						'.HtmlTag::create( 'input', NULL, [
							'type'		=> "text",
							'name'		=> "authorCompany",
							'id'		=> "input_authorCompany",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->authorCompany ?? '', ENT_QUOTES, 'UTF-8' ),
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						] ).'
					</div>
					<div class="span6">
						<label for="input_authorName">'.$w->labelAuthorUrl ?? ''.'</label>
						'.HtmlTag::create( 'input', NULL, [
							'type'		=> "text",
							'name'		=> "authorUrl",
							'id'		=> "input_authorUrl",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->authorUrl ?? '', ENT_QUOTES, 'UTF-8' ),
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						] ).'
					</div>
				</div>
			</div>
			<div class="span4">
				<label for="input_imprint">'.$words->edit->labelImprint.'</label>
				'.HtmlTag::create( 'textarea', htmlentities( $template->imprint ?? '', ENT_QUOTES, 'UTF-8' ), [
					'name'		=> 'imprint',
					'id'		=> 'input_imprint',
					'class'		=> 'span12',
					'rows'		=> 12,
					'required'	=> 'required',
					'readonly'	=> $isUsed ? 'readonly' : NULL,
					'disabled'	=> $isUsed ? 'disabled' : NULL,
				] ).'
			</div>
		</div>
		'.$buttons.'
	</div>
</div>';

return HtmlTag::create( 'div', [
	HtmlTag::create( 'div', [
		$panelEdit,
		HtmlTag::create( 'div', [
			HtmlTag::create( 'div', [$panelCopy], ['class' => 'span6'] ),
			HtmlTag::create( 'div', [$panelRemove, $panelNewsletters], ['class' => 'span6'] ),
		], ['class' => 'row-fluid'] ),
	], ['class' => 'span7'] ),
	HtmlTag::create( 'div', [
		$panelPreview
	], ['class' => 'span5'] ),
], ['class' => 'row-fluid'] );
