<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] ).'&nbsp;';

$wf	= (object) $words['index.filter'];
$wl	= (object) $words['index.list'];

$optCategory	= ['' => $wf->optionAll];
foreach( $categories as $category )
	$optCategory[$category->categoryId]	= $category->title;
$optCategory	= HtmlElements::Options( $optCategory, $filterCategoryId );

$optSize	= ['' => $wf->optionAll];
foreach( ['S', 'M', 'L', 'XL'] as $size )
	$optSize[$size]	= $size;
$optSize	= HtmlElements::Options( $optSize, $filterSize );


$filterLanguage		= '';
if( count( $languages ) > 1 ){
	$optLanguage	= HtmlElements::Options( array_combine( $languages, $languages ), $language );
	$filterLanguage	= '
		<div class="row-fluid">
			<div class="span12">
				<label for="input_language" class="mandatory">'.$words['filter']['labelLanguage'].'</label>
				<select name="language" id="input_language" class="span12" onchange="this.form.submit();">'.$optLanguage.'</select>
			</div>
		</div>';
}
else
	$filterLanguage		= HtmlTag::create( 'input', NULL, ['type' => 'hidden', 'name' => 'language', 'value' => $language] );


$panelFilter	= '
<div class="content-panel">
	<h3>'.$wf->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/catalog/clothing/article/filter" method="post">
			'.$filterLanguage.'
			<div class="row-fluid">
				<div class="span12">
					<label for="input_categoryId">'.$wf->labelCategory.'</label>
					<select name="categoryId" id="input_categoryId" class="span12">'.$optCategory.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_size">'.$wf->labelSize.'</label>
					<select name="size" id="input_size" class="span12">'.$optSize.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_limit">'.$wf->labelLimit.'</label>
					<input type="number" min="1" max="200" step="1" name="limit" id="input_limit" class="span12" value="'.htmlentities( $filterLimit, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="filter" class="btn btn-small btn-primary">'.$wf->buttonFilter.'</button>
				<a href="./manage/catalog/clothing/article/filter/reset" class="btn btn-small btn-inverse">'.$wf->buttonReset.'</a>
			</div>
		</form>
	</div>
</div>';

$rows	= [];
foreach( $articles as $article ){
	$link	= HtmlTag::create( 'a', $article->title, [
		'href'	=> './manage/catalog/clothing/article/edit/'.$article->articleId,
	] );
	$image	= '';
	if( $article->image ){
		$image	= HtmlTag::create( 'div', NULL, array(
			'style'		=> 'background-image: url('.$path.$article->image.')',
			'class'		=> 'catalog-clothing-thumbnail'
		) );
	}

	$rows[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $image, ['class' => 'cell-article-image'] ),
		HtmlTag::create( 'td', $link, ['class' => 'cell-article-title'] ),
		HtmlTag::create( 'td', $categoryMap[$article->categoryId]->title, ['class' => 'cell-article-category'] ),
		HtmlTag::create( 'td', $article->form, ['class' => 'cell-article-form'] ),
		HtmlTag::create( 'td', $article->color, ['class' => 'cell-article-color'] ),
		HtmlTag::create( 'td', $article->size, ['class' => 'cell-article-size'] ),
		HtmlTag::create( 'td', $article->quantity, ['class' => 'cell-article-quantity'] ),
		HtmlTag::create( 'td', $article->price.'&euro;', ['class' => 'cell-article-price', 'style' => 'text-align: right'] ),
	) );
}
$colgroup	= HtmlElements::ColumnGroup( ['40', '', '15%', '10%', '10%', '10%', '5%', '10%'] );
$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
	HtmlTag::create( 'th', '' ),
	HtmlTag::create( 'th', $wl->headTitle ),
	HtmlTag::create( 'th', $wl->headCategory ),
	HtmlTag::create( 'th', $wl->headForm ),
	HtmlTag::create( 'th', $wl->headColor ),
	HtmlTag::create( 'th', $wl->headSize ),
	HtmlTag::create( 'th', $wl->headQuantity ),
	HtmlTag::create( 'th', $wl->headPrice, ['style' => 'text-align: right'] ),
) ) );
$tbody	= HtmlTag::create( 'tbody', $rows );
$table	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-fixed'] );

$buttonAdd		= HtmlTag::create( 'a', $iconAdd.$wl->buttonAdd, [
	'href'	=> './manage/catalog/clothing/article/add',
	'class'	=> 'btn btn-success',
] );

$pagination	= new \CeusMedia\Bootstrap\PageControl( './manage/catalog/clothing/article', $page, ceil( $total / $filterLimit ) );

$panelList		= '
<style>
table td.cell-article-image {
	padding: 0;
	}
.catalog-clothing-thumbnail {
	width: 40px;
	height: 36px;
	background-position: center center;
	background-size: cover;
	}

</style>
<div class="content-panel">
	<h3>'.$wl->heading.'</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
			'.$pagination.'
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
