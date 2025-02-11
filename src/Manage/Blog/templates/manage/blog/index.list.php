<?php

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<string,array<string,string>> $words */
/** @var array<object> $posts */

$w		= (object) $words['index'];

$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'icon-plus icon-white'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) )
	$iconAdd	= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-plus'] );

$list	= '<div class=alert">'.$w->empty.'</div>';



if( $posts ){
	$list	= [];
	foreach( $posts as $post ){
		$link	= HtmlTag::create( 'a', $post->title, ['href' => './manage/blog/edit/'.$post->postId] );
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $words['states'][$post->status] ),
			HtmlTag::create( 'td', $post->category ? $post->category->title : '' ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( "", "15%", "20%" );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [$w->headTitle, $w->headStatus, $w->headCategory] ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
}

Icon::$defaultSet	= 'FontAwesome';
$pagination	= new PageControl( "./manage/blog/", $page ?? 1, $pages ?? 0 );

return '
<div class="content-panel content-panel-list">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$list.'
		<div class="buttonbar">
			<div class="btn-group">
				<a href="./manage/blog/add" class="btn btn-success">'.$iconAdd.'&nbsp;neuer Eintrag</a>
			</div>
			<div class="btn-group">
				'.$pagination.'
			</div>
		</div>
	</div>
</div>';
