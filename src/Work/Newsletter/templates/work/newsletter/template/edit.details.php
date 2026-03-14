<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_Work_Newsletter_Template $view */
/** @var object $words */
/** @var bool $tabbedLinks */
/** @var Entity_Newsletter_Template $template */
/** @var array $newsletters */
/** @var array<Entity_Mail_Template> $mailTemplates */
/** @var string $templateId */
/** @var bool $isUsed */
/** @var array $buttons */

/** @var object $w */
$w		= $words->edit;

$iconCopy		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-clone'] ).'&nbsp;';
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
$iconPreview	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-eye'] ).'&nbsp;';
$optStatus		= HtmlElements::Options( $words->states, $template->status );

/*  --  PANEL: NEWSLETTERS  --  */

$listNewsletters	= '<em><small class="muted">Nicht verwendet.</small></em>';
if( [] !== $newsletters ){
	$listNewsletters	= [];
	foreach( $newsletters as $newsletter ){
		$link	= HtmlTag::create( 'a', $newsletter->title, ['href' => './work/newsletter/edit/'.$newsletter->newsletterId] );
		$listNewsletters[]	= HtmlTag::create( 'li', $link );
	}
	$listNewsletters	= HtmlTag::create( 'ul', $listNewsletters, ['class' => 'unstyled nav nav-pills nav-stacked'] );
}

$optMailTemplate = [];
foreach( $mailTemplates as $mailTemplate )
	$optMailTemplate[$mailTemplate->mailTemplateId]	= $mailTemplate->title;
$optMailTemplate	= HtmlElements::Options( $optMailTemplate, $template->mailTemplateId );

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
$buttonCopy		= HtmlTag::create( 'a', $iconCopy.$w->buttonCopy, [
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
$buttonRemove	= HtmlTag::create( 'a', $iconRemove.$w->buttonRemove, [
	'class'		=> "btn btn-danger",
	'href'		=> $isUsed ? '#' : "./work/newsletter/template/remove/".$templateId,
	'disabled'	=> $isUsed ? 'disabled' : NULL,
	'onclick'	=> $isUsed ? "alert('".$w->buttonRemoveDisabled."'); return false;" : NULL,
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
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span6">
				<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
<!--			<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ).'"/>-->
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
			<div class="span6">
				<label for="input_mailTemplateId" class="mandatory">'.$w->labelMailTemplateId.'</label>
						'.HtmlTag::create( 'select', $optMailTemplate, [
		'name'		=> 'mailTemplateId',
		'id'		=> 'input_mailTemplateId',
		'class'		=> 'span12',
	] ).'
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
				<label for="input_senderAddress" class="mandatory">'.$w->labelSenderAddress.'</label>
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
				<label for="input_senderName" class="mandatory">'.$w->labelSenderName.'</label>
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
<!--				<div class="row-fluid">
					<div class="span4">
						<label for="input_status">'.$w->labelStatus.'</label>
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
					<div class="span12">
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
				</div>
				<div class="row-fluid">
					<div class="span12">
						<label for="input_authorName">'.$w->labelAuthorEmail.'</label>
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
					<div class="span12">
						<label for="input_authorCompany">'.$w->labelAuthorCompany.'</label>
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
				</div>
				<div class="row-fluid">
					<div class="span12">
						<label for="input_authorUrl">'.$w->labelAuthorUrl.'</label>
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
			<div class="span6">
				<div class="row-fluid">
					<div class="span12">
						<label for="input_imprint">'.$w->labelImprint.'</label>
						'.HtmlTag::create( 'textarea', htmlentities( $template->imprint ?? '', ENT_QUOTES, 'UTF-8' ), [
							'name'		=> 'imprint',
							'id'		=> 'input_imprint',
							'class'		=> 'span12',
							'rows'		=> 9,
							'required'	=> 'required',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						] ).'
					</div>
				</div>
				<div class="row-fluid">
					<div class="span12">
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
								'.$w->labelReady.'
						</label>
					</div>
				</div>
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
	], ['class' => 'span8'] ),
	HtmlTag::create( 'div', [
		$view->renderHtmlPreviewPanel( $template, 'scale-2x half-size' ),
		$view->renderTextPreviewPanel( $template, 'scale-2x half-size' ),
	], ['class' => 'span4'] ),
], ['class' => 'row-fluid'] );
