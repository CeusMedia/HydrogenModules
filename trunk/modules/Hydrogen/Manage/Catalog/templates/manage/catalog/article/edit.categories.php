<?php

$listCategories	= '<small class="muted"><em>Noch keine Kategorie(n) zugewiesen.</em></small>';
$listCategories	= '<div class="label not-label-warning">Noch keine Kategorie(n) zugewiesen.</div>';
$listCategories	= '<div class="alert alert-error">Noch keine Kategorie(n) zugewiesen.</div>';

if( $articleCategories ){
	$listCategories	= array();
	foreach( $articleCategories as $item ){
		$labelVolume	= $item->volume ? '<span class="muted">Band </span>'.$item->volume : "";
		if( $item->parentId ){
			$label	= '
			<div class="autocut" style="font-size: 1.1em; font-weight: bold">
				<small class="muted">'.$item->parent->rank.'.</small>
				'.$item->parent->label_de.'
			</div>
			<div class="autocut" style="margin-left: 1em">
				<small class="muted">'.$item->rank.'.</small>
				'.$item->label_de.'
			</div>
			<div style="font-size: 0.9em; font-weight: lighter">'.$labelVolume.'</div>
			';
		}
		else{
			$label	= '
			<div class="autocut" style="font-size: 1.1em; font-weight: bold">
				<small class="muted">'.$item->rank.'.</small>
				'.$item->label_de.'
			</div>
			<div class="autocut"></div>
			<div style="font-size: 0.9em; font-weight: lighter">'.$labelVolume.'</div>
			';
		}
		
		$urlRemove	= './manage/catalog/article/removeCategory/'.$article->article_id.'/'.$item->categoryId;
		$buttonRemove	= '<a class="btn btn-mini btn-danger" href="'.$urlRemove.'"><i class="icon-remove icon-white"></i></a>';
		$listCategories[]	= '<tr>
		<td>'.$label.'</td>
		<td><div class="pull-right">'.$buttonRemove.'</div></td>
	</tr>';
	}

	$listCategories	= '<table class="table table-condensed">
		'.UI_HTML_Elements::ColumnGroup( '', '60px' ).'
		<thead>
<!--			<tr>
				<th>Kategorie</th>
				<th></th>
			</tr>-->
		</thead>
		<tbody>
			'.join( $listCategories ).'
		</tbody>
	</table>';
}

$subs	= array();
foreach( $categories as $item ){
	if( $item->parentId ){
		if( !isset( $subs[$item->parentId] ) )
			$subs[$item->parentId]	= array();
		$subs[$item->parentId][$item->rank]	= $item;
		ksort( $subs[$item->parentId] );
	}
}
foreach( $subs as $parentId => $items ){
}

$optCategory	= array();
foreach( $categories as $item ){
	if( !$item->parentId ){
		$sub	= "";
		if( isset( $subs[$item->categoryId] ) ){
			$list	= array();
			foreach( $subs[$item->categoryId] as $sub )
				if( !array_key_exists( $sub->categoryId, $articleCategories ) )
					$list[]	= '<option value="'.$sub->categoryId.'"> - '.$sub->label_de.'</option>';
			$sub	= join( $list );
		}
		$optCategory[$item->rank]	= '<option value="'.$item->categoryId.'">'.$item->label_de.'</option>'.$sub;
	}
}
ksort( $optCategory );
$optCategory	= join( $optCategory );

return '
<!--  Manage: Catalog: Article: Categories  -->
	<div class="row-fluid">
		<h4>Kategorien</h4>
		'.$listCategories.'
	</div>
	<div class="row-fluid">
		<h4>Kategorie zuweisen</h4>
		<form action="./manage/catalog/article/addCategory/'.$article->article_id.'" method="post">
			<div class="row-fluid">
				<label for="input_categoryId">Kategorie</label>
				<select class="span12" name="categoryId" id="input_categoryId">'.$optCategory.'</select>
			</div>
			<div class="row-fluid">
				<label for="input_volume">Band</label>
				<input type="text" class="span4" name="volume" id="input_volume"/>
			</div>
			<div class="buttonbar">
				<button class="btn btn-small btn-success" type="submit" name="save"><i class="icon-ok icon-white"></i> speichern</button>
			</div>
		</form>
	</div>
<!--  /Manage: Catalog: Article: Categories  -->
';
?>
