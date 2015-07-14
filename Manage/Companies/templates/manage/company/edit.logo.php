<?php
$w	= (object) $words['edit-logo'];

$iconSave	= HTML::Icon( 'ok', TRUE );
$iconOpen	= HTML::Icon( 'folder-open', TRUE );

$logo	= '<div><em><small class="muted">Kein Logo vorhanden.</small></em></div>';
if( $company->logo ){
	$logo	= UI_HTML_Tag::create( 'img', NULL, array(
		'src'	=> $frontend->getPath().'images/companies/'.$company->logo,
		'class'	=> 'thumbnail',
	) );
}

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$logo.'
		<hr/>
		<form action="./manage/company/uploadLogo/'.$company->companyId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_image">'.$w->labelImage.'</label>
					'.View_Helper_Input_File::render( 'image', $iconOpen, 'Datei auswählen...' ).'
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-toolbar">
					<button type="submit" name="save" class="btn btn-primary btn-small">'.$iconSave.'&nbsp;'.$w->buttonSave.'</button>
				</div>
			</div>
		</form>
	</div>
</div>';
?>
