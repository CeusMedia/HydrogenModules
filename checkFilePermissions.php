<?php
$mode	= "775";

/*  -----------------------------------------------------------  */

checkFolder( "./", $mode );
function checkFolder( $path, $perms ){
	$index	= new DirectoryIterator( $path );
	foreach( $index as $entry ){
		if( $entry->isDot() || $entry->getFilename() == ".git" )
			continue;
		if( $entry->isDir() )
			checkFolder( $entry->getPathname(), $perms );
		else{
			$filePerms	= substr( sprintf( '%o', fileperms( $entry->getPathname() ) ), -3 );
			if( $filePerms !== $perms )
				print( "\n".$filePerms."->".$perms." @ File ".$entry->getPathname() );
		}
	}
}

