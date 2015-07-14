<?php
$w		= (object) $words['edit'];

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonBack, array(
	'href'		=> './manage/relocation',
	'class'		=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-success',
) );
$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, array(
	'href'		=> './manage/relocation/remove/'.$relocation->relocationId,
	'class'		=> 'btn btn-small btn-danger',
	'onclick'	=> htmlentities( 'return confirm("'.$w->buttonRemove_confirm.'")', ENT_QUOTES, 'UTF-8' ),
) );

$optStatus		= UI_HTML_Elements::Options( $words['states'], $relocation->status );
$urlService		= $env->url.'info/relocation/'.$relocation->relocationId;
$urlShortcut	= $env->url.$shortcut.'/'.$relocation->relocationId;

$panelEdit	= '
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/relocation/edit/'.$relocation->relocationId.'" method="post">
					<div class="row-fluid">
							<div class="span10">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $relocation->title, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
						<div class="span2">
							<label for="input_status">'.$w->labelStatus.'</label>
							<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_url">'.$w->labelUrl.' <small class="muted">'.$w->labelUrl_suffix.'</small></label>
							<input type="text" name="url" id="input_url" class="span12" value="'.htmlentities( $relocation->url, ENT_QUOTES, 'UTF-8' ).'" required="required"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label>'.$w->labelUrlService.' <small class="muted">'.$w->labelUrlService_suffix.'</small></label>
							<input type="text" class="span12" value="'.$urlService.'" readonly="readonly"/>
						</div>
					</div>
					<div class="row-fluid" '.( $shortcut ? '' : 'style="display: none"' ).'>
						<div class="span12">
							<label>'.$w->labelUrlShortcut.' <small class="muted">'.$w->labelUrlShortcut_suffix.'</small></label>
							<input type="text" class="span12" value="'.$urlShortcut.'" readonly="readonly"/>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
						'.$buttonRemove.'
					</div>
				</form>
			</div>
		</div>
';

$panelInfo	= $this->loadTemplateFile( 'manage/relocation/edit.info.php' );

extract( $view->populateTexts( array( 'edit.top', 'edit.bottom' ), 'html/manage/relocation/' ) );

return $textEditTop.'
<div class="row-fluid">
	<div class="span8">
		'.$panelEdit.'
	</div>
	<div class="span4">
		'.$panelInfo.'
	</div>
</div>
'.$textEditBottom;
