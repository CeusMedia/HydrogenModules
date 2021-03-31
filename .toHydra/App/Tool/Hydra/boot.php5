<?php
/*  --  BOOT  --  */
function _cmRealize( $constant, $variable, $default ){
	$var	= isset( $GLOBALS[$variable] ) ? $GLOBALS[$variable] : $default;
	if( strlen( $constant ) ){
		if( !defined( $constant ) )
			define( $constant, $var );
		$var	= constant( $constant );
	}
	return $var;
}
/*  --  LOAD LIBRARIES  --  */
$pathLibraries	= isset( $pathLibraries ) ? $pathLibraries : '/var/www/lib/';
_cmRealize( 'CML_PATH', 'pathLibraries', $pathLibraries );
require_once CML_PATH.'cmClasses/'._cmRealize( 'CMC_VERSION', 'versionCMC', 'trunk' ).'/autoload.php5';
require_once CML_PATH.'cmFrameworks/'._cmRealize( 'CMF_VERSION', 'versionCMF', 'trunk' ).'/autoload.php5';
require_once CML_PATH.'cmModules/'._cmRealize( 'CMM_VERSION', 'versionCMM', 'trunk' ).'/autoload.php5';

/*  --  LOAD CLASS PATHS  --  */
if( isset( $autoloadPaths ) && is_array( $autoloadPaths ) )
	foreach( $autoloadPaths as $library )
		if( $library = (object) array_merge( array( 'path' => NULL, 'exts' => NULL, 'prefix' => NULL ), $library ) )	//  
			CMC_Loader::registerNew( NULL, $library->prefix, $library->path );

?>
