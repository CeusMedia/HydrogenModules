<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/**
 *	Template for Univerlag Frontend.
 *	@author			Christian Würker <Christian.Wuerker@CeuS-Media.de>
 */

/** @var Environment $env */
/** @var array $words */
/** @var object[] $articles */
/** @var object $author */

$w		= (object) $words['author'];
$language	= $env->getLanguage()->getLanguage();
$helper		= new View_Helper_Catalog_Bookstore( $env );

$list		= '<div><em class="muted">Bisher keine Veröffentlichungen.</em></div><br/>';
if( $articles ){
	$list	= [];
	foreach( $articles as $article )
		$list[]	= $helper->renderArticleListItem( $article );
	$list	= HtmlTag::create( 'div', $list, ['class' => 'articleList'] );
}

$about	= '';
$image	= '';
$link	= '';
$id		= str_pad( $author->authorId, 5, 0, STR_PAD_LEFT );
$text	= nl2br( htmlentities( $author->description, ENT_COMPAT, 'UTF-8' ) );
$name	= $author->lastname;
if( $author->firstname )
	$name	= $author->firstname." ".$name;

if( $author->reference ){
	if( preg_match( "/@/", $author->reference ) ){
		$label	= $author->reference;
		$href	= "mailto:".$author->reference;
		$link	= HtmlTag::create( 'a', $label, ['href' => $href] );
	}
	else{
		$label	= preg_replace( "/^http:\/\/(.+)\/$/", "\\1", $author->reference );
		$href	= $author->reference;
		$link	= HtmlTag::create( 'a', $label, ['href' => $href, 'target' => '_blank'] );
	}
	$link	= '<b>Adresse: '.$link.'</b>';
}
if( $author->image ){
	$image	= '<img class="img-polaroid" src="file/bookstore/author/'.$author->image.'" alt="'.$name.'" title="'.$name.'"/>';
	$about	= '
	<div id="author-about">
		<br/>
		<div style="float: left; width: 260px; height: 200px; text-align: center; padding-top: 8px">'.$image.'</div>
		<div style="margin-left: 260px; margin-right: 60px">
			<h3>'.$name.'</h3>
			'.$text.'<br/>
			<br/>
			'.$link.'

		</div>
		<div style="clear: left"></div>
		<br/>
	</div>';
}
else if( $text ){
	$about	= '
	<div id="author-about">
		<br/>
		<h3>'.$name.'</h3>
		'.$text.'<br/>
		<br/>
		'.$link.'
		<br/>
	</div>
	<br/>';
}


return '
<!--
<style>
#author-about img {
	border: 1px solid rgb(95, 95, 95);
	box-shadow: 2px 2px 6px rgb(159, 159, 159);
}
</style>
-->
<div>
	<h2>'.$w->heading.'</h2>
	'.$about.'
	<h3>'.$w->caption." ".$name.'</h3>
	'.$list.'
	<a href="#" onclick="history.back()" class="btn btn-small">'.$w->link_back.'</a>
</div>
';
