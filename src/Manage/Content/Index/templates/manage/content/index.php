<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/*
$tabs	 = array(
	'manage/content'			=> 'Übersicht',
	'manage/content/link'		=> 'Links',
	'manage/content/document'	=> 'Dokumente',
	'manage/content/image'		=> 'Bilder',
);
$current	= 'manage/content/link';
$list	= [];
foreach( $tabs as $key => $value ){
	$class	= $key == $current ? 'active' : NULL;
	$link	= HtmlTag::create( 'a', $value, ['href' => $key] );
	$list[]	= HtmlTag::create( 'li', $link, ['class' => $class] );
}
$tabs	= HtmlTag::create( 'ul', $list, ['class' => 'nav nav-tabs'] );
*/

$content	= "[Index]";
return '
<div class="tabbable" id="tabs-manage-content">
	'.$this->renderTabs().'
	<div class="tab-content">
		'.$content.'
	</div>
</div>
';
