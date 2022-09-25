<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w			= (object) $view->getWords( 'index.list', 'manage/catalog/article' );

$list		= $this->renderList( $articles, isset( $article ) ? $article->articleId : NULL );

$heading	= $w->heading;
if( !empty( $w->heading_title ) )
	$heading	= HtmlTag::create( 'abbr', $heading, array( 'title' => $w->heading_title ) );

return '
<div class="content-panel">
	<div class="pull-right">
		<a href="./manage/catalog/article/add" class="btn btn-mini btn-success" title="'.htmlentities( $w->buttonAdd, ENT_QUOTES, 'UTF-8' ).'"><i class="icon-plus icon-white"></i></a>
	</div>
	<h4>'.$heading.'</h4>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
