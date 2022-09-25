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
	$link	= HtmlTag::create( 'a', $value, array( 'href' => $key ) );
	$list[]	= HtmlTag::create( 'li', $link, array( 'class' => $class ) );
}
$tabs	= HtmlTag::create( 'ul', $list, array( 'class' => 'nav nav-tabs' ) );
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
?>
