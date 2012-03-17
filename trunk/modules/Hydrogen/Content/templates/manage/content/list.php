<?php
$files	= $this->getData( 'files' );
$path	= $this->getData( 'path' );
if( !$files )
	throw new RuntimeException( 'No file data provided by controller' );
$list	= array();
foreach( $files as $file ){
	$key	= substr( $file->getPathname(), strlen( $path ) );
	$link	= UI_HTML_Tag::create( 'a', $key, array( 'href' => './manage/content/edit/'.base64_encode( $key ) ) );
	$list[$key]	= UI_HTML_Tag::create( 'li', $link );
}
ksort( $list );
$list	= UI_HTML_Tag::create( 'ul', implode( $list ), array( 'class' => 'content-files' ) );

#return $list;

$buttonAdd	= UI_HTML_Elements::LinkButton( './manage/content/add', 'neue Datei', 'button add' );

return '
<fieldset>
	<legend>Dateien</legend>
	'.$list.'
</fieldset>
';
?>
