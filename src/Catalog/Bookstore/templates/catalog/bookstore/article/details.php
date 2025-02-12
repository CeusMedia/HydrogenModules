<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array $words */
/** @var object $article */
/** @var object $category */
/** @var object[] $authors */
/** @var object[] $documents */
/** @var object[] $tags */
/** @var ?string $uriCoverLarge */

$a			= clone( $article );
$w			= (object) $words['article'];
$w->isn		= $a->series ? $w->issn : $w->isbn;
$helper		= new View_Helper_Catalog_Bookstore( $env );

$a->description = View_Helper_Text::applyFormat( $a->description );
$a->recension	= View_Helper_Text::applyFormat( $a->recension );

$a->description	= View_Helper_Catalog_Bookstore::applyLinks( $env, $a->description );
$a->recension	= View_Helper_Catalog_Bookstore::applyLinks( $env, $a->recension );

$a->description	= View_Helper_Text::applyLinks( $a->description );
$a->recension	= View_Helper_Text::applyLinks( $a->recension );

$a->price			= $helper->formatPrice( $a->price )."&nbsp;&euro;";
$a->title			= View_Helper_Text::applyFormat( $a->title );
$a->subtitle		= View_Helper_Text::applyFormat( $a->subtitle );
$a->description		= View_Helper_Text::applyExpandable( $a->description, 200, '...<br/><span class="btn btn-mini">mehr</span>', '<br/><span class="btn btn-mini">weniger</span>' );
$a->recension		= View_Helper_Text::applyExpandable( $a->recension, 200, '<span class="btn btn-mini">mehr</span>', '<span class="btn btn-mini">weniger</span>' );
$a->volume			= $category->volume ? $w->volume."&nbsp;".$category->volume : "";
$a->status			= HtmlTag::create( 'span', $words['status'][$article->status], ['class' => 'status_'.$article->status] );

$list		= [];
foreach( $authors as $author )
	$list[] = $helper->renderAuthorLink( $author );
$a->authors	= implode( "<br/>", $list );

/*  LANGUAGES  */
if( empty( $a->language ) )
	$a->language	= 'de';
$languages		= [];
$wordsLanguage	= $words['languages'];
foreach( explode( ',', $a->language ) as $language )
	$languages[]	= $wordsLanguage[trim( $language )];
$a->languages		= implode( ', ', $languages );


$a->documents		= '<small class="muted"><em>keine</em></small>';
if( $documents ){
	$list	= [];
	foreach( $documents as $document ){
		$link	= $helper->renderDocumentLink( $document );
		$list[]	= HtmlTag::create( 'li', $link, ['class' => 'document'] );
	}
	$a->documents	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled documents documentList'] );
}

//  --  LIST: FACTS (next to image)  --  //
$a->tags			= "-";
if( $tags ){
	$list	= [];
	foreach( $tags as $tag ){
		$label	= $tag->tag;
		$link	= HtmlTag::create( 'a', $label, ['href' => $helper->getTagUri( $tag ), 'class' => 'link-article-tag'] );
		$list[]	= HtmlTag::create( 'li', $link, ['class' => 'article-tag-list-item'] );
	}
	$a->tags	= HtmlTag::create( 'ul', $list, ['class' => 'article-tag-list'] );
}


$keys	= [
	'author'		=> 'authors',
	'language'		=> 'languages',
	'publication'	=> 'publication',
	'digestion'		=> 'digestion',
	'size'			=> 'size',
	'isn'			=> 'isn',
	'price'			=> 'price',
	'documents'		=> 'documents',
//	'tags'			=> 'tags',
	'status'		=> 'status',
];
$list	= [];
foreach( $keys as $key => $value )
	if( !empty( $value ) )
		$list[]	= '<dt>'.$w->$key.'</dt><dd>'.$a->$value.'</dd>';
$listFacts	= '<dl class="dl-horizontal">'.join( $list ).'</dl>';

//  --  LIST: DEFINITIONS (full width) --  //
$a->tags			= "-";
if( $tags ){
	$list	= [];
	foreach( $tags as $tag ){
		$list[]	= HtmlTag::create( 'a', $tag->tag, [
			'href'	=> $helper->getTagUri( $tag ),
			'class'	=> 'link-article-tag',
		] );
	}
	$a->tags	= join( ", ", $list );
}

$tagList	= [];
foreach( $tags as $tag )
	$tagList[]	= $tag->tag;
$tagList	= join( ', ', $tagList );

$keys	= [
	'description',
//	'recension',
//	'tags'
];
if( $env->getRequest()->has( 'testing123' ) ){
	$keys[]	= 'tags';
}


$list	= [];
foreach( $keys as $key )
	if( !empty( $a->$key ) )
		$list[]	= '<dt>'.$w->$key.'</dt><dd>'.$a->$key.'</dd>';
$definitions	= '<dl class="dl-horizontal">'.join( $list ).'</dl>';

$image			= $helper->renderArticleImage( $article, "" );

if( $uriCoverLarge ){
	$image		= HtmlTag::create( 'a', $image, [
		'href'		=> $uriCoverLarge,
		'class'		=> 'fancybox',
		'title'		=> 'Cover: '.$article->title,
	] );
}

return '
<div id="panel-catalog-article-details">
	<div class="visible-tablet visible-desktop">
		<div class="row-fluid">
			<div class="span9">
				'.$listFacts.'
			</div>
			<div class="span3" style="text-align: center">
				<div class="image">'.$image.'</div>
			</div>
		</div>
	</div>
	<div class="row-fluid visible-phone">
		<div style="float: left; width: 200px">
			<br/>
			<div class="image">'.$image.'</div>
		</div>
		<div style="float: left; min-width: 100px">
			'.$listFacts.'
		</div>
		<div class="clearfloat"></div>
	</div>
	'.$definitions.'
</div>
<script>
let ViewHelperText = {
	toggleLongText: function(toggler){
		let parent = $(toggler).parent().parent();
//console.log(parent);
		$("div.text_more", parent).toggle();
		$("div.text_less", parent).toggle();
		return false;
	}
};
</script>
<div style="display: none">'.$tagList.'</div>
';
