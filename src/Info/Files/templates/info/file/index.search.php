<?php
$env->getPage()->js->addScriptOnReady( 'jQuery("#input_search").focus();' );

return '
<div class="content-panel">
	<h4>'.$words['search']['heading'].'</h4>
	<div class="content-panel-inner">
		<form action="./info/file'.( $folderId ? '/'.$folderId : '' ).'" method="get">
			<label for="input_search">'.$words['search']['labelQuery'].'</label>
			<input type="search" name="search" id="input_search" value="'.htmlentities( $search, ENT_QUOTES, 'UTF-8' ).'"/>
			<div class="buttonbar">
				<button type="submit" name="doSearch" class="btn btn-small"><i class="fa fa-fw fa-search"></i> '.$words['search']['buttonSave'].'</button>
			</div>
		</form>
	</div>
</div>';
