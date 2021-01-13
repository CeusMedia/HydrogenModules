<?php

$w		= (object) $words['export'];

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconExport		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-download' ) );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				<form action="./work/newsletter/template/export/'.$template->newsletterTemplateId.'" method="post">
					<div class="row-fluid">
						<div class="span8">
							<div class="row-fluid">
								<div class="span6">
									<label for="input_title">'.$w->labelTitle.'</label>
									'.UI_HTML_Tag::create( 'input', NULL, array(
										'name'		=> 'title',
										'type'		=> 'text',
										'id'		=> 'input_title',
										'class'		=> 'span12',
										'value'		=> htmlentities( $template->title, ENT_QUOTES, 'UTF-8' ),
									) ).'
								</div>
								<div class="span2">
									<label for="input_version">'.$w->labelVersion.'</label>
									'.UI_HTML_Tag::create( 'input', NULL, array(
										'type'		=> 'text',
										'name'		=> 'version',
										'id'		=> 'input_version',
										'class'		=> 'span12',
										'value'		=> 1,
									) ).'
								</div>
								<div class="span2">
									<label for="input_license">'.$w->labelLicense.'</label>
									'.UI_HTML_Tag::create( 'input', NULL, array(
										'type'		=> 'text',
										'name'		=> 'license',
										'id'		=> 'input_license',
										'class'		=> 'span12',
										'value'		=> '',
									) ).'
								</div>
							</div>
							<div class="row-fluid">
								<div class="span6">
									<label for="input_senderName">'.$w->labelSenderName.'</label>
									'.UI_HTML_Tag::create( 'input', NULL, array(
										'type'		=> 'text',
										'name'		=> 'senderName',
										'id'		=> 'input_senderName',
										'class'		=> 'span12',
										'value'		=> htmlentities( $template->senderName, ENT_QUOTES, 'UTF-8' ),
									) ).'
								</div>
								<div class="span6">
									<label for="input_senderAddress">'.$w->labelSenderAddress.'</label>
									'.UI_HTML_Tag::create( 'input', NULL, array(
										'type'		=> 'text',
										'name'		=> 'senderAddress',
										'id'		=> 'input_senderAddress',
										'class'		=> 'span12',
										'value'		=> htmlentities( $template->senderAddress, ENT_QUOTES, 'UTF-8' ),
									) ).'
								</div>
							</div>
							<div class="row-fluid">
								<div class="span6">
									<label for="input_authorName">'.$w->labelAuthorName.'</label>
									'.UI_HTML_Tag::create( 'input', NULL, array(
										'type'		=> 'text',
										'name'		=> 'authorName',
										'id'		=> 'input_authorName',
										'class'		=> 'span12',
										'value'		=> htmlentities( $template->authorName, ENT_QUOTES, 'UTF-8' ),
									) ).'
								</div>
								<div class="span6">
									<label for="input_authorEmail">'.$w->labelAuthorEmail.'</label>
									'.UI_HTML_Tag::create( 'input', NULL, array(
										'type'		=> 'text',
										'name'		=> 'authorEmail',
										'id'		=> 'input_authorEmail',
										'class'		=> 'span12',
										'value'		=> htmlentities( $template->authorEmail, ENT_QUOTES, 'UTF-8' ),
									) ).'
								</div>
							</div>
							<div class="row-fluid">
								<div class="span6">
									<label for="input_authorCompany">'.$w->labelAuthorCompany.'</label>
									'.UI_HTML_Tag::create( 'input', NULL, array(
										'type'		=> 'text',
										'name'		=> 'authorCompany',
										'id'		=> 'input_authorCompany',
										'class'		=> 'span12',
										'value'		=> htmlentities( $template->authorCompany, ENT_QUOTES, 'UTF-8' ),
									) ).'
								</div>
								<div class="span6">
									<label for="input_authorUrl">'.$w->labelAuthorUrl.'</label>
									'.UI_HTML_Tag::create( 'input', NULL, array(
										'type'		=> 'text',
										'name'		=> 'authorUrl',
										'id'		=> 'input_authorUrl',
										'class'		=> 'span12',
										'value'		=> htmlentities( $template->authorUrl, ENT_QUOTES, 'UTF-8' ),
									) ).'
								</div>
							</div>
						</div>
						<div class="span4">
							<label for="input_imprint">'.$w->labelImprint.'</label>
							'.UI_HTML_Tag::create( 'textarea', htmlentities( $template->imprint, ENT_QUOTES, 'UTF-8' ), array(
								'name'		=> 'imprint',
								'id'		=> 'input_imprint',
								'class'		=> 'span12',
								'rows'		=> 11,
							) ).'
						</div>
					</div>
					<div class="buttonbar">
						'.UI_HTML_Tag::create( 'a', $iconCancel.'zurück', array(
							'href'		=> './work/newsletter/template/'.$template->newsletterTemplateId,
							'class'		=> 'btn',
						) ).'
						'.UI_HTML_Tag::create( 'button', $iconExport.'&nbsp;exportieren', array(
							'type'		=> 'submit',
							'name'		=> 'save',
							'class'		=> 'btn btn-primary',
						) ).'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';