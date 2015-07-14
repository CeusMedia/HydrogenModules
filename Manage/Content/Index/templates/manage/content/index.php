<?php
/*
$tabs	 = array(
	'manage/content'			=> 'Übersicht',
	'manage/content/link'		=> 'Links',
	'manage/content/document'	=> 'Dokumente',
	'manage/content/image'		=> 'Bilder',
);
$current	= 'manage/content/link';
$list	= array();
foreach( $tabs as $key => $value ){
	$class	= $key == $current ? 'active' : NULL;
	$link	= UI_HTML_Tag::create( 'a', $value, array( 'href' => $key ) );
	$list[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
}
$tabs	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'nav nav-tabs' ) );
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
