<?php

$listTags	= "";
$listTags	= '<div class="alert alert-error">Noch kein Schlagwort vergeben.</div>';

if( $articleTags ){
	$listTags	= array();
	foreach( $articleTags as $item ){
		$listTags[]	= '<tr>
	<td>'.$item->tag.'</td>
	<td><div class="pull-right"><a class="btn btn-mini btn-danger" href="./manage/catalog/article/removeTag/'.$article->articleId.'/'.$item->articleTagId.'"><i class="icon-remove icon-white"></i></a></div></td>
</tr>';
	}

	$listTags	= '<table class="table table-condensed">
	'.UI_HTML_Elements::ColumnGroup( '', '60px' ).'
	<thead>
<!--		<tr>
			<th>Schlagwort</th>
			<th></th>
		</tr>-->
	</thead>
	<tbody>
		'.join( $listTags ).'
	</tbody>
</table>';
}

return '
<!--  Manage: Catalog: Article: Tags  -->
	<div class="row-fluid">
		<div class="span6">
			<h4>Schlagwörter</h4>
			'.$listTags.'
		</div>
		<div class="span6">
			<h4>Schlagwort vergeben</h4>
			<label for="input_tag">neues Schlagwort</label>
			<input class="span12" type="text" name="tag" id="input_tag"/><br/>
			<button type="button" class="btn btn-small btn-success" onclick="addArticleTag('.$article->articleId.')"><i class="icon-plus icon-white"></i> hinzufügen</button>
		</div>
	</div>
<!--  Manage: Catalog: Article: Tags  -->
';
?>
