<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) ).'&nbsp;';

$panelFilter	= '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/clothing/article/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<em class="muted">... kommt noch ...</em>
				</div>
			</div>
		</form>
	</div>
</div>';

$rows	= [];
foreach( $categories as $category ){
	$link	= HtmlTag::create( 'a', $category->title, array(
		'href'	=> './manage/catalog/clothing/category/edit/'.$category->categoryId,
	) );
	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $link, array( 'class' => 'cell-category-title' ) ),
	) );
}
$tbody	= HtmlTag::create( 'tbody', $rows );
$table	= HtmlTag::create( 'table', $tbody, array( 'class' => 'table table-fixed' ) );

$buttonAdd		= HtmlTag::create( 'a', $iconAdd.'neue Kategorie', array(
	'href'	=> './manage/catalog/clothing/category/add',
	'class'	=> 'btn btn-success',
) );

$panelList	= '
<div class="content-panel">
	<h3>Kategorien</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
		</div>
	</div>
</div>';

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFilter.'
	</div>
	<div class="span9">
		'.$panelList.'
	</div>
</div>';
