<?php

$w	= (object) $words['import'];

if( !count( $index ) )
	return '<h2>'.$w->heading.'</h2><em class="muted">'.$w->noEntries.'</em><br/><br/>';

return '
<!--	<h2>Import</h2>-->
	<h3><span class="muted">'.$w->headingPrefix.'</span> '.$w->heading.'</h3>
	<div class="row-fluid">
		<div class="span6">
			<form class="form-horizontal" action="./manage/catalog/gallery/import" method="post">
				<div class="control-group">
					<label class="control-label" for="folders">'.$w->labelFolders.'</label>
					<div class="controls">
						'.$list.'
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="folders">'.$w->labelPrice.'</label>
					<div class="controls">
						<div class="input-append">
							<input type="text" class="span4" name="price"/>
							<span class="add-on">&euro;</span>
						</div>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						'.new CeusMedia\Bootstrap\Button\Link( './manage.php5', $w->buttonCancel, 'btn-small', 'arrow-left' ).'
						'.new CeusMedia\Bootstrap\Button\Submit( 'import', $w->buttonImport, 'btn-small btn-success', 'ok' ).'
					</div>
				</div>
			</form>
		</div>
		<div class="span6">
		</div>
	</div>
';
