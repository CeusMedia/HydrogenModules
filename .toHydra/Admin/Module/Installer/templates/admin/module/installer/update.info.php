<?php
return '
<fieldset>
	<legend class="info">Informationen</legend>
	<dl class="general">
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
</fieldset>';
?>
