<?php

$w			= (object) $words['tab-database'];
$wf			= (object) $words['tab-database-add'];

$count	= 0;
$sql	= '<br/><div>'.$w->listNone.'</div><br/>';
if( $module->sql ){
	$sql	= array();
	foreach( $module->sql as $type => $content ){
		$count++;
		$code	= UI_HTML_Tag::create( 'xmp', trim( $content ) );
		$sql[]	= UI_HTML_Tag::create( 'dt', $type ).UI_HTML_Tag::create( 'dd', $code, array( 'class' => 'sql' ) );
	}
	$sql	= UI_HTML_Tag::create( 'dl', join( $sql ) );
}

$optEvent	= UI_HTML_Elements::Options( $words['database-events'] );
$optType	= UI_HTML_Elements::Options( $words['database-types'] );

$panelAdd	= '
	<form action="./admin/module/editor/addSql/'.$moduleId.'?tab=database" method="post">
		<fieldset>
			<legend class="icon add">'.$wf->legend.'</legend>
			<ul class="input">
				<li class="column-left-60">
					<label for="input_event">'.$wf->labelEvent.'</label><br/>
					<select name="event" id="input_event" class="max">'.$optEvent.'</select>
				</li>
				<li class="column-left-40">
					<label for="input_type">'.$wf->labelType.'</label><br/>
					<select name="type" id="input_type" class="max">'.$optType.'</select>
				</li>
				<li>
					<label for="input_ddl">'.$wf->labelDDL.'</label><br/>
					<textarea name="ddl" id="input_ddl" class="max" rows="10"></textarea>
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::Button( 'addSql', $wf->buttonAdd, 'button add' ).'
			</div>
		</fieldset>
	</form>
';

return '
<style>
dd.sql {
	max-height: 200px;
	overflow: auto;
	}

</style>
<div class="column-left-70">
	'.$sql.'
</div>
<div class="column-left-30">
	'.$panelAdd.'
</div>
<div class="column-clear"></div>';
?>