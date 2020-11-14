<?php

$w		= $words->edit;

$iconCopy		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-clone' ) ).'&nbsp;';
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) ).'&nbsp;';
$iconPreview	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) ).'&nbsp;';
$optStatus		= UI_HTML_Elements::Options( $words->states, $template->status );

$listNewsletters	= '<em><small class="muted">Nicht verwendet.</small></em>';
if( $newsletters ){
	$listNewsletters	= array();
	foreach( $newsletters as $newsletter ){
		$link	= UI_HTML_Tag::create( 'a', $newsletter->title, array( 'href' => './work/newsletter/edit/'.$newsletter->newsletterId ) );
		$listNewsletters[]	= UI_HTML_Tag::create( 'li', $link, array() );
	}
	$listNewsletters	= UI_HTML_Tag::create( 'ul', $listNewsletters, array( 'class' => 'unstyled nav nav-pills nav-stacked' ) );
}

/*  --  PANEL: PREVIEW: HTML  --  */
$urlPreview		= './work/newsletter/template/preview/html/'.$template->newsletterTemplateId;
$iframeHtml		= UI_HTML_Tag::create( 'iframe', '', array(
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
) );
$buttonPreviewHtml	= UI_HTML_Tag::create( 'button', $iconPreview.'Vorschau', array(
	'type'			=> 'button',
	'class'			=> 'btn btn-info btn-mini',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
) );
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
$iframeText		= UI_HTML_Tag::create( 'iframe', '', array(
	'src'			=> $urlPreview,
	'frameborder'	=> '0',
) );
$buttonPreviewText	= UI_HTML_Tag::create( 'button', $iconPreview.'Vorschau', array(
	'type'			=> 'button',
	'class'			=> 'btn btn-info btn-mini',
	'data-toggle'	=> 'modal',
	'data-target'	=> '#modal-preview',
	'onclick'		=> 'ModuleWorkNewsletter.showPreview("'.$urlPreview.'");',
) );
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
		<div style="max-height: 400px; overflow-x: none; overflow-y: auto;">
			'.$listNewsletters.'
		</div>
	</div>
</div>';


/*  --  PANEL: COPY  --  */
$buttonCopy		= UI_HTML_Tag::create( 'a', $iconCopy.$words->edit->buttonCopy, array(
	'class'		=> "btn btn-success",
	'href'		=> "./work/newsletter/template/add?templateId=".$templateId
) );
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
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.$words->edit->buttonRemove, array(
	'class'		=> "btn btn-danger",
	'href'		=> $isUsed ? '#' : "./work/newsletter/template/remove/".$templateId,
	'disabled'	=> $isUsed ? 'disabled' : NULL,
	'onclick'	=> $isUsed ? "alert('".$words->edit->buttonRemoveDisabled."'); return false;" : NULL,
) );

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
						'.UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> 'text',
							'name'		=> 'title',
							'id'		=> 'input_title',
							'class'		=> 'span12',
							'value'		=> $template->title,
							'required'	=> 'required',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
						) ).'
					</div>
				</div>
<!--				<div class="row-fluid">
					<div class="span4">
						<label for="input_status">'.$words->edit->labelStatus.'</label>
						'.UI_HTML_Tag::create( 'select', $optStatus, array(
							'name'		=> 'status',
							'id'		=> 'input_status',
							'class'		=> 'span12',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						) ).'
					</div>
				</div>-->
				<div class="row-fluid">
					<div class="span6">
						<label for="input_senderAddress" class="mandatory">'.$words->edit->labelSenderAddress.'</label>
						'.UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> "text",
							'name'		=> "senderAddress",
							'id'		=> "input_senderAddress",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->senderAddress, ENT_QUOTES, 'UTF-8' ),
							'required'	=> 'required',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						) ).'
					</div>
					<div class="span6">
						<label for="input_senderName" class="mandatory">'.$words->edit->labelSenderName.'</label>
						'.UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> "text",
							'name'		=> "senderName",
							'id'		=> "input_senderName",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->senderName, ENT_QUOTES, 'UTF-8' ),
							'required'	=> 'required',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						) ).'
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<label for="input_status" class="checkbox">
						'.UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> 'checkbox',
							'name'		=> 'status',
							'value'		=> Model_Newsletter_Template::STATUS_READY,
							'id'		=> 'input_status',
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
							'checked'	=> $template->status >= Model_Newsletter_Template::STATUS_READY ? 'checked' : NULL,
						) ).'
							'.$words->edit->labelReady.'
						</label>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<label for="input_authorName">'.$w->labelAuthorName.'</label>
						'.UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> "text",
							'name'		=> "authorName",
							'id'		=> "input_authorName",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->authorName, ENT_QUOTES, 'UTF-8' ),
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						) ).'
					</div>
					<div class="span6">
						<label for="input_authorName">'.$w->labelAuthorEmail.'</label>
						'.UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> "text",
							'name'		=> "authorEmail",
							'id'		=> "input_authorEmail",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->authorEmail, ENT_QUOTES, 'UTF-8' ),
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						) ).'
					</div>
				</div>
				<div class="row-fluid">
					<div class="span6">
						<label for="input_authorName">'.$w->labelAuthorCompany.'</label>
						'.UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> "text",
							'name'		=> "authorCompany",
							'id'		=> "input_authorCompany",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->authorCompany, ENT_QUOTES, 'UTF-8' ),
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						) ).'
					</div>
					<div class="span6">
						<label for="input_authorName">'.$w->labelAuthorUrl.'</label>
						'.UI_HTML_Tag::create( 'input', NULL, array(
							'type'		=> "text",
							'name'		=> "authorUrl",
							'id'		=> "input_authorUrl",
							'class'		=> "span12",
							'value'		=> htmlentities( $template->authorUrl, ENT_QUOTES, 'UTF-8' ),
							'readonly'	=> $isUsed ? 'readonly' : NULL,
							'disabled'	=> $isUsed ? 'disabled' : NULL,
						) ).'
					</div>
				</div>
			</div>
			<div class="span4">
				<label for="input_imprint">'.$words->edit->labelImprint.'</label>
				'.UI_HTML_Tag::create( 'textarea', htmlentities( $template->imprint, ENT_QUOTES, 'UTF-8' ), array(
					'name'		=> 'imprint',
					'id'		=> 'input_imprint',
					'class'		=> 'span12',
					'rows'		=> 12,
					'required'	=> 'required',
					'readonly'	=> $isUsed ? 'readonly' : NULL,
					'disabled'	=> $isUsed ? 'disabled' : NULL,
				) ).'
			</div>
		</div>
		'.$buttons.'
	</div>
</div>';

$content	= '
<div class="row-fluid">
	<div class="span7">
		'.$panelEdit.'
		<div class="row-fluid">
			<div class="span6">
				'.$panelCopy.'
			</div>
			<div class="span6">
				'.$panelRemove.'
				'.$panelNewsletters.'
			</div>
		</div>
		<div class="row-fluid"></div>
	</div>
	<div class="span5">
		'.$panelPreview.'
	</div>
</div>
<div class="row-fluid"></div>';

return $content;
?>
