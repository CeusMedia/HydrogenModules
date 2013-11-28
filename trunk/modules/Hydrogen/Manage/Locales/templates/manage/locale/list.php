<?php
$files	= $this->getData( 'files' );
$path	= $this->getData( 'pathLocale' );

$w	= (object) $words['list'];

if( !$files )
	throw new RuntimeException( 'No file data provided by controller' );

$list	= array();
foreach( $files as $file ){
	$classes	= array();
	$pathName		= substr( $file->getPathname(), strlen( $path ) );
	if( $filePath === $pathName )
		$classes[]	= 'active';
	$label		= $pathName;
	$fileExt	= pathinfo( $pathName, PATHINFO_EXTENSION );										//  try to get file extension
	if( $fileExt ){																					//  file has an extension
		$label	= substr( $pathName, 0, -1 * ( 1 + strlen( $fileExt ) ) );							//  cut off extenstion from label
		$label	.= '<span class="file-ext">.'.$fileExt.'</span>';									//  and append it again with wrapper
	}

	$url	= './manage/locale/edit/'.base64_encode( $pathName );									//
	$link	= UI_HTML_Tag::create( 'a', $pathName, array( 'href' => $url ) );						//
	if( !is_writeable( $file->getPathname() ) ){													//  file ist not writable
		$classes[]	= "not-writeable error danger";													//
		$link		= UI_HTML_Tag::create( 'span', $label );										//
	}
	$attributes			= array( 'class' => join( " ", $classes ) );
	$list[$pathName]	= UI_HTML_Tag::create( 'li', $link, $attributes );
}
ksort( $list );
$list	= UI_HTML_Tag::create( 'ul', implode( $list ), array( 'class' => 'list-locale nav nav-pills nav-stacked' ) );

#return $list;

return '
<h3>'.$w->heading.'</h3>
'.$list.'
';
?>
