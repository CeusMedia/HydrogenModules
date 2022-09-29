<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$tabsMain		= $tabbedLinks ? $this->renderMainTabs() : '';

$optTemplate	= ['' => '-'];
foreach( $templates as $item )
	$optTemplate[$item->newsletterTemplateId]	= $item->title;
$optTemplate	= HtmlElements::Options( $optTemplate, $template->templateId );

$iconCancel	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-arrow-left"] ).'&nbsp;';
$iconSave	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-check"] ).'&nbsp;';

$panelAdd	= '
<div class="content-panel">
	<h3>'.$words->add->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				<form action="./work/newsletter/template/add" method="post">
					<div class="row-fluid">
						<div class="span8">
							<div class="row-fluid">
								<div class="span8">
									<label for="input_title" class="mandatory">'.$words->add->labelTitle.'</label>
									<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ).'" required/>
								</div>
								<div class="span4">
									<label for="input_templateId">'.$words->add->labelTemplateId.'</label>
									<select name="templateId" id="input_templateId" class="span12 has-optionals">'.$optTemplate.'</select>
								</div>
							</div>
							<div class="row-fluid optional templateId templateId-">
								<div class="span6">
									<label for="input_senderAddress" class="mandatory">'.$words->add->labelSenderAddress.'</label>
									<input type="text" name="senderAddress" id="input_senderAddress" class="span12" value="'.htmlentities( $template->senderAddress, ENT_QUOTES, 'UTF-8' ).'" required/>
								</div>
								<div class="span6">
									<label for="input_senderName" class="mandatory">'.$words->add->labelSenderName.'</label>
									<input type="text" name="senderName" id="input_senderName" class="span12" value="'.htmlentities( $template->senderName, ENT_QUOTES, 'UTF-8' ).'" required/>
								</div>
							</div>
						</div>
						<div class="span4 optional templateId templateId-">
							<label for="input_imprint" class="mandatory">'.$words->add->labelImprint.'</label>
							'.HtmlTag::create( 'textarea', htmlentities( $template->imprint, ENT_QUOTES, 'UTF-8' ), array(
								'name'		=> 'imprint',
								'id'		=> 'input_imprint',
								'class'		=> 'span12',
								'rows'		=> 8,
								'required'	=> 'required',
							) ).'
						</div>
					</div>
					<div class="row-fluid optional templateId templateId-">
						<div class="span12">
							<label for="input_plain" class="mandatory">'.$words->add->labelPlain.'</label>
							<textarea name="plain" id="input_plain" class="span12 CodeMirror-auto" rows="10" required="required">'.htmlentities( $template->plain, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid optional templateId templateId-">
						<div class="span12">
							<label for="input_html" class="mandatory">'.$words->add->labelHtml.'</label>
							<textarea name="html" id="input_html" class="span12 CodeMirror-auto" rows="10" required="required">'.htmlentities( $template->html, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid optional templateId templateId-">
						<div class="span12">
							<label for="input_style" class="mandatory">'.$words->add->labelStyle.'</label>
							<textarea name="style" id="input_style" class="span12 CodeMirror-auto" rows="10" required="required">'.htmlentities( $template->style, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid optional templateId templateId-">
						<div class="span12">
							<label for="input_imprint" class="mandatory">'.$words->add->labelImprint.'</label>
							<textarea name="style" id="input_style" class="span12 CodeMirror-auto" rows="10" required="required">'.htmlentities( $template->style, ENT_QUOTES, 'UTF-8' ).'</textarea>
						</div>
					</div>
					<div class="row-fluid">
						<div class="buttonbar">
							<a href="./work/newsletter/template/index" class="btn btn-small">'.$iconCancel.$words->add->buttonCancel.'</a>
							<button type="submit" class="btn btn-primary" name="save">'.$iconSave.$words->add->buttonSave.'</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';

extract( $view->populateTexts( ['above', 'bottom', 'top'], 'html/work/newsletter/template/add/', ['words' => $words] ) );

return $textTop.'
<script>
$(document).ready(function(){
//	ModuleWorkNewsletter.init();
});
</script>
<div class="newsletter-content">
	'.$tabsMain.'
	'.$textAbove.'
	'.$panelAdd.'
</div>
'.$textBottom;
