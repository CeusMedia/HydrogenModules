<?php
return '';

$optPath	= array_merge( array( '' ), $paths );
$optPath	= array_combine( $optPath, $optPath );
$optPath	= UI_HTML_Elements::Options( $optPath, $pathName );

$w	= (object) $words['filter'];

return '
<form action="./manage/content" method="post">
	<fieldset>
		<legend>'.$w->legend.'</legend>
		<label for="input_filter_query">'.$w->labelQuery.'</label><br/>
		<input type="text" name="filter_query" id="input_filter_query" class="max"/><br/>
		<label for="input_filter_path">'.$w->labelPath.'</label><br/>
		<select name="filter_path" id="input_filter_path" class="max">'.$optPath.'</select><br/>
		<button type="submit" class="button filter"><span>'.$w->buttonFilter.'</span></button>
		<button type="button" class="button reset"><span>'.$w->buttonReset.'</span>
	</fieldset>
</form>';
?>
