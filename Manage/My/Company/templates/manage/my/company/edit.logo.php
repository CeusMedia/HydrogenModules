<?php

$iconSave	= HTML::Icon( 'ok', TRUE );
$iconOpen	= HTML::Icon( 'folder-open', TRUE );

$logo	= '<div><em><small class="muted">Kein Logo vorhanden.</small></em></div>';
if( $company->logo ){
	$logo	= UI_HTML_Tag::create( 'img', NULL, array(
		'src'	=> 'images/companies/'.$company->logo,
		'class'	=> 'thumbnail',
	) );
}

return '
<div class="content-panel">
	<h3>Logo</h3>
	'.$logo.'
	<div class="content-panel-inner">
		<form action="./manage/my/company/uploadLogo/'.$company->companyId.'" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_image">'.$words['uploadLogo']['labelImage'].'</label>
					<div class="row-fluid">
						'.View_Helper_Input_File::render( 'image', $iconOpen, 'Datei auswählen...' ).'
					</div>
				</div>
			</div>
			<div class="buttonbar btn-toolbar">
				<button type="submit" name="save" class="btn btn-primary btn-small">'.$iconSave.'&nbsp;'.$words['uploadLogo']['buttonSave'].'</button>
			</div>
		</form>
	</div>
</div>';
?>
