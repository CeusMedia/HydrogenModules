<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconUp		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-chevron-up' ) );
$iconDown	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-chevron-down' ) );
$iconRemove	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$panelCategories	= '<div class="alert alert-error">Noch keine Kategorie zugewiesen.</div>';

if( $articleCategories ){
	$listCategories	= [];
	foreach( $articleCategories as $item ){
		$labelVolume	= $item->volume ? ''.$item->volume : "-";
		if( $item->parentId ){
			$label	= '
			<div class="autocut" style="font-size: 1.1em; font-weight: bold">
				<small class="muted">'.$item->parent->rank.'.</small>
				<a href="./manage/catalog/bookstore/category/edit/'.$item->parent->categoryId.'">'.$item->parent->label_de.'</a>
			</div>
			<div class="autocut" style="margin-left: 1em">
				<small class="muted">'.$item->rank.'.</small>
				<a href="./manage/catalog/bookstore/category/edit/'.$item->categoryId.'">'.$item->label_de.'</a>
			</div>
			<div style="font-size: 0.99em; font-weight: not-lighter"><span class="muted">Band:</span> '.$labelVolume.' | <span class="muted">Rang:</span> '.$item->rank.'</div>
			';
		}
		else{
			$label	= '
			<div class="autocut" style="font-size: 1.1em; font-weight: bold">
				<small class="muted">'.$item->rank.'.</small>
				<a href="./manage/catalog/bookstore/category/edit/'.$item->categoryId.'">'.$item->label_de.'</a>
			</div>
			<div class="autocut"></div>
			<div style="font-size: 0.9em; font-weight: lighter"><span class="muted">Band:</span> '.$labelVolume.' | <span class="muted">Rang:</span> '.$item->rank.'</div>
			';
		}
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, array(
			'href'		=> './manage/catalog/bookstore/article/removeCategory/'.$article->articleId.'/'.$item->categoryId,
			'class'		=> 'btn btn-mini btn-danger',
			'onclick'	=> 'if(!confirm(\'Wirklich ?\'))return false;',
		) );
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
	$panelCategories	= '
<div class="content-panel">
	<div class="content-panel-inner">
		<div class="row-fluid">
			<h4>Kategorien</h4>
			'.$listCategories.'
		</div>
	</div>
</div>
<hr/>';
}

$subs	= [];
foreach( $categories as $item ){
	if( $item->parentId ){
		if( !isset( $subs[$item->parentId] ) )
			$subs[$item->parentId]	= [];
		$subs[$item->parentId][$item->rank]	= $item;
		ksort( $subs[$item->parentId] );
	}
}
foreach( $subs as $parentId => $items ){
}

$optCategory	= [];
foreach( $categories as $item ){
	if( !$item->parentId ){
		$sub	= "";
		if( isset( $subs[$item->categoryId] ) ){
			$list	= [];
			foreach( $subs[$item->categoryId] as $sub )
				if( 1 || !array_key_exists( $sub->categoryId, $articleCategories ) )
					$list[]	= '<option value="'.$sub->categoryId.'"> - '.$sub->label_de.'</option>';
			$sub	= join( $list );
		}
		$optCategory[$item->rank]	= '<option value="'.$item->categoryId.'">'.$item->label_de.'</option>'.$sub;
	}
}
ksort( $optCategory );
$optCategory	= join( $optCategory );

$panelAdd	= '
<div class="content-panel">
	<div class="content-panel-inner form-changes-auto">
		<h4>Kategorie zuweisen</h4>
		<form action="./manage/catalog/bookstore/article/addCategory/'.$article->articleId.'" method="post">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_categoryId">Kategorie</label>
					<select class="span12" name="categoryId" id="input_categoryId">'.$optCategory.'</select>
				</div>
				<div class="span2">
					<label for="input_volume">Band</label>
					<input type="text" class="span12" name="volume" id="input_volume"/>
				</div>
				<div class="span2">
					<label for="input_rank">Rang</label>
					<input type="number" class="span12" name="rank" id="input_rank" value=""/>
				</div>
			</div>
			<div class="buttonbar">
				<button class="btn btn-primary" type="submit" name="save"><i class="icon-ok icon-white"></i> speichern</button>
			</div>
		</form>
	</div>
</div>
<script>
jQuery("#input_categoryId").on("change", function(){
	var value = jQuery("#input_categoryId").val();
	jQuery.ajax({
		url: "./manage/catalog/bookstore/category/ajaxGetNextRank/"+value,
		method: "post",
		dataType: "json",
		success: function(json){
			jQuery("#input_rank").val(json);
		}
	});
});
</script>';

return '
<!--  Manage: Catalog: Article: Categories  -->
'.$panelCategories.'
'.$panelAdd.'
<!--  /Manage: Catalog: Article: Categories  -->';
?>
