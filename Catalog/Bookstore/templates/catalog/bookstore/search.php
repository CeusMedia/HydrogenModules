<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$helper	= new View_Helper_Catalog_Bookstore( $env );
$words	= (object) $words['search'];

$optAuthor	= array( '' => 'alle' );
foreach( $authors as $author )
	$optAuthor[$author->authorId]	= $author->lastname.', '.$author->firstname;
$optAuthor	= HtmlElements::Options( $optAuthor, $searchAuthorId );

$optCategory	= array( '' => 'alle' );
foreach( $categories as $category )
	$optCategory[$category->categoryId]	= $category->label_de;
$optCategory	= HtmlElements::Options( $optCategory, $searchCategoryId );

$list	= '';
$pages	= '';
if( $searchTerm || $searchAuthorId ){
	$list	= '<small class="muted"><em>'.$words->empty.'</em></small>';
	if( $articles ){
		$list	= [];
		foreach( $articles as $article )
			$list[]	= $helper->renderArticleListItem( $article );
		$list	= HtmlTag::create( 'div', $list, array( 'class' => 'articleList' ) );
		$pages	= new \CeusMedia\Bootstrap\Nav\PageControl( './catalog/bookstore/search', $page, ceil( $total / $limit ) );
	}
}

extract( $view->populateTexts( array( 'search.top', 'search.bottom' ), 'html/catalog/bookstore/' ) );

return $textSearchTop.'
<h2>'.$words->heading.'</h2>
<form action="./catalog/bookstore/search" method="get">
	<div class="row-fluid">
		<div class="span4">
			<label for="input_term">'.$words->labelTerm.'</label>
			<input type="text" name="term" id="input_term" class="span12" value="'.$searchTerm.'"/>
		</div>
		<div class="span4">
			<label for="input_authorId">'.$words->labelAuthorId.'</label>
			<select name="authorId" id="input_authorId" class="span12">'.$optAuthor.'</select>
		</div>
		<div class="span4">
			<label>'.$words->labelOptions.'</label>
			<label class="checkbox"><input type="checkbox" name="status" id="input_status" value="1" '.( $searchStatus ? 'checked="checked"' : '' ).'/>&nbsp;'.$words->labelStatus.'</label>
			<label class="checkbox"><input type="checkbox" name="picture" id="input_picture" value="1" '.( $searchPicture ? 'checked="checked"' : '' ).'/>&nbsp;'.$words->labelPicture.'</label>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span8">
			<label for="input_categoryId">'.$words->labelCategoryId.'</label>
			<select name="categoryId" id="input_categoryId" class="span12">'.$optCategory.'</select>
		</div>
	</div>
<!--	<div class="buttonbar">-->
		<button type="submit" name="search" class="btn btn"><i class="icon-search"></i> '.$words->buttonSearch.'</button>
		'.$pages.'
<!--	</div>-->
</form>
'.$list.'
'.$pages.$textSearchBottom;
?>
