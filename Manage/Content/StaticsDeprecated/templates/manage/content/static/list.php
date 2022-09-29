<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$files	= $view->getData( 'files' );
$path	= $view->getData( 'pathContent' );

$w	= (object) $words['list'];

if( !$files )
	throw new RuntimeException( 'No file data provided by controller' );

$list	= [];
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
	$link	= HtmlTag::create( 'a', $filePath, ['href' => $url] );	//
	if( !is_writeable( $file->getPathname() ) ){								//  file ist not writable
		$class	= "not-writeable";												//
		$link	= HtmlTag::create( 'span', $label );						//
	}
	$list[$filePath]	= HtmlTag::create( 'li', $link, ['class' => $class] );
}
ksort( $list );
$list	= HtmlTag::create( 'ul', implode( $list ), ['class' => 'list-html'] );

#return $list;

return '
<fieldset>
	<legend>'.$w->legend.'</legend>
	'.$list.'
</fieldset>
';
?>
