<?php

$list	= listFolder( __DIR__."/", "tinymce" );
$list	= join( "\n", $list );
print PHP_EOL.$list.PHP_EOL.PHP_EOL;

function listFolder( $basePath, $path ){
	$list	= array();
	$index	= new DirectoryIterator( $basePath.$path );
	foreach( $index as $entry ){
		if( $entry->isDot() )
			continue;
		if( $entry->isDir() ){
			$sublist	= listFolder( $basePath, $path."/".$entry->getFilename() );
			foreach( $sublist as $item )
				$list[]	= $item;
		}
		else{
			$list[]	= "\t\t<script>".$path."/".$entry->getFilename()."</script>";
		}
	}
	return $list;
}

