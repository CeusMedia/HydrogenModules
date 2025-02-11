<?php
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var Dictionary $moduleConfig */
/** @var array<string,array<string,string>> $words */
/** @var array<object> $posts */

$list	= [];
foreach( $posts as $post ){
	$list[]		= View_Info_Blog::renderPostAbstractStatic( $this->env, $post );
}
$list	= HtmlTag::create( 'div', $list, ['class' => 'blog-post-list'] );

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/info/blog/' ) );

return $textIndexTop.'
<div class="content-panel content-panel-list">
	<h3>Blog-Beiträge</h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>
'.$textIndexBottom;
