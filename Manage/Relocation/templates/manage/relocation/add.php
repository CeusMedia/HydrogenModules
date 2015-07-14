<?php
$w	= (object) $words['add'];

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );

$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonBack, array(
	'href'		=> './manage/relocation',
	'class'		=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-success',
) );

$optStatus		= UI_HTML_Elements::Options( $words['states'], (int) $relocation->status );
$helper			= new View_Helper_TimePhraser( $env );

$panelAdd	= '
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/relocation/add" method="post">
					<div class="row-fluid">
							<div class="span10">
							<label for="input_title">'.$w->labelTitle.'</label>
							<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $relocation->title, ENT_QUOTES, 'UTF-8' ).'"/>
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
					<div class="buttonbar">
						'.$buttonCancel.'
						'.$buttonSave.'
					</div>
				</form>
			</div>
		</div>
';

extract( $view->populateTexts( array( 'add.top', 'add.bottom', 'add.right' ), 'html/manage/relocation/' ) );

return $textAddTop.'
<div class="row-fluid">
	<div class="span8">
		'.$panelAdd.'
	</div>
	<div class="span4">
		'.$textAddRight.'
	</div>
</div>
'.$textAddBottom;
