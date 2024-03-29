<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $words['tab-database'];
$wf			= (object) $words['tab-database-add'];

$count	= 0;
$list	= '<br/><div>'.$w->listNone.'</div><br/>';
if( $module->sql ){
	$list	= [];
	foreach( $module->sql as $type => $sql ){
		if( !strlen( trim( $sql->sql ) ) )
			continue;
		$count++;
		$code		= HtmlTag::create( 'xmp', trim( $sql->sql ) );
		$url		= './admin/module/editor/removeSql/'.$moduleId;

		$version	= $sql->event === 'update' ? '<br/>Version: '.$sql->version: '';
		$remove		= HtmlElements::LinkButton( $url, 'entfernen', 'button icon remove', 'Wirklich?' );
		$label		= ucFirst( $sql->event ).$version.'<br/>DBMS: '.$sql->type.'<br/>'.$remove;
		$list[]		= HtmlTag::create( 'dt', $label );
		$list[]		= HtmlTag::create( 'dd', $code, ['class' => 'sql'] );
	}
	$list	= HtmlTag::create( 'dl', join( $list ), ['class' => 'database'] );
}

$types	= ['*' => $words['tab-database']['allTypes']] + $words['database-types'];

$optEvent	= HtmlElements::Options( $words['database-events'] );
$optType	= HtmlElements::Options( $types );

$panelAdd	= '
	<form action="./admin/module/editor/addSql/'.$moduleId.'?tab=database" method="post">
		<fieldset>
			<legend class="icon add">'.$wf->legend.'</legend>
			<ul class="input">
				<li class="column-left-60">
					<label for="input_event">'.$wf->labelEvent.'</label><br/>
					<select name="event" id="input_event" class="max" onchange="showOptionals(this);">'.$optEvent.'</select>
				</li>
				<li class="column-left-40">
					<label for="input_type">'.$wf->labelType.'</label><br/>
					<select name="type" id="input_type" class="max">'.$optType.'</select>
				</li>
				<li class="column-clear column-left-30 optional event-update">
					<label for="input_version">'.$wf->labelVersion.'</label><br/>
					<input type="text" name="version" id="input_version" class="s numeric"/>
				</li>
				<li class="column-clear">
					<label for="input_ddl">'.$wf->labelDDL.'</label><br/>
					<textarea name="ddl" id="input_ddl" class="max" rows="10"></textarea>
				</li>
			</ul>
			<div class="buttonbar">
				'.HtmlElements::Button( 'addSql', $wf->buttonAdd, 'button add' ).'
			</div>
		</fieldset>
	</form>
	<script>
$(document).ready(function(){
	showOptionals($("#input_event").get(0));
});
	</script>
';

return '
<style>
dd.sql {
	max-height: 200px;
	overflow: auto;
	}

</style>
<div class="column-left-70">
	'.$list.'
</div>
<div class="column-left-30">
	'.$panelAdd.'
</div>
<div class="column-clear"></div>';
?>
