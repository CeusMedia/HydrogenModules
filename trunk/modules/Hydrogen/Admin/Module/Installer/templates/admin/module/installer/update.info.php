<?php

unset( $moduleLocal->icon );
unset( $moduleLocal->sql );
unset( $moduleSource->icon );
unset( $moduleSource->sql );

return '
<fieldset>
	<legend class="info">Informationen</legend>
	<dl>
		<dt>Modul</dt>
		<dd>'.$moduleLocal->title.'</dd>
		<dt>Quelle</dt>
		<dd>'.$moduleLocal->source.'</dd>
		<dt>Ausgangsversion</dt>
		<dd>'.( $moduleLocal->versionInstalled? $moduleLocal->versionInstalled : '?' ).'</dd>
		<dt>Zielversion </dt>
		<dd>'.( $moduleLocal->versionAvailable ? $moduleLocal->versionAvailable : '?' ).'</dd>
	</dl>
	<div class="clearfix"></div>
</fieldset>
<fieldset>
	<legend class="info">Modul: Lokal</legend>
	<div style="height: 300px; overflow: auto;">
		'.print_m( $moduleLocal, NULL, NULL, TRUE ).'
	</div>
</fieldset>
<fieldset>
	<legend class="info">Modul: Source</legend>
	<div style="height: 300px; overflow: auto;">
		'.print_m( $moduleSource, NULL, NULL, TRUE ).'
	</div>
</fieldset>';
?>