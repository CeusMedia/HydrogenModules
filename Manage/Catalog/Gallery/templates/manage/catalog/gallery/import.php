<?php

if( !count( $index ) )
	return '<h2>Import</h2><em class="muted">Nichts zu importieren.</em><br/><br/>';

return '
<!--	<h2>Import</h2>-->
	<h3><span class="muted">Galerien:</span> Import</h3>
	<div class="row-fluid">
		<div class="span6">
			<form class="form-horizontal" action="./manage/catalog/gallery/import" method="post">
				<div class="control-group">
					<label class="control-label" for="folders">Ordner</label>
					<div class="controls">
						'.$list.'
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="folders">Preis pro Bild</label>
					<div class="controls">
						<div class="input-append">
							<input type="text" class="span4" name="price"/>
							<span class="add-on">&euro;</span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						'.new CMM_Bootstrap_LinkButton( './manage.php5', 'zurück', 'btn-small', 'arrow-left' ).'
						'.new CMM_Bootstrap_SubmitButton( 'import', 'importieren', 'btn-small btn-success', 'ok' ).'
					</div>
				</div>
			</form>
		</div>
		<div class="span6">
		</div>
	</div>
';
?>