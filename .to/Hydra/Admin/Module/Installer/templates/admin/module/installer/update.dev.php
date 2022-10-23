<?php

unset( $moduleLocal->icon );
unset( $moduleLocal->sql );
unset( $moduleSource->icon );
unset( $moduleSource->sql );

return '
<div class="column-left-50">
	<fieldset>
		<legend class="info">Modul: Lokal</legend>
		<div style="height: 300px; overflow: auto;">
			'.print_m( $moduleLocal, NULL, NULL, TRUE ).'
		</div>
	</fieldset>
</div>
<div class="column-left-50">
	<fieldset>
		<legend class="info">Modul: Source</legend>
		<div style="height: 300px; overflow: auto;">
			'.print_m( $moduleSource, NULL, NULL, TRUE ).'
		</div>
	</fieldset>
</div>';
?>
