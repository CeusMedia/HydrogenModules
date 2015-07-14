<?php
$files	= $view->getData( 'files' );
$path	= $view->getData( 'pathContent' );

$w	= (object) $words['list'];

if( !$files )
	throw new RuntimeException( 'No file data provided by controller' );

$list	= array();
foreach( $files as $file ){
	$class		= "";
	$filePath	= substr( $file->getPathname(), strlen( $path ) );
	$label		= $filePath;
	$fileExt	= pathinfo( $filePath, PATHINFO_EXTENSION );					//  try to get file extension
	if( $fileExt ){																//  file has an extension
		$label	= substr( $filePath, 0, -1 * ( 1 + strlen( $fileExt ) ) );		//  cut off extenstion from label
		$label	.= '<span class="file-ext">.'.$fileExt.'</span>';				//  and append it again with wrapper
	}

	$url	= './manage/content/static/edit/'.base64_encode( $filePath );				//
	$link	= UI_HTML_Tag::create( 'a', $filePath, array( 'href' => $url ) );	//
	if( !is_writeable( $file->getPathname() ) ){								//  file ist not writable
		$class	= "not-writeable";												//
		$link	= UI_HTML_Tag::create( 'span', $label );						//
	}
	$list[$filePath]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
}
ksort( $list );
$list	= UI_HTML_Tag::create( 'ul', implode( $list ), array( 'class' => 'list-html' ) );

#return $list;

return '
<fieldset>
	<legend>'.$w->legend.'</legend>
	'.$list.'
</fieldset>
';
?>
