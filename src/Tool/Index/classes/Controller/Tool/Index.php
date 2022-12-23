<?php

use CeusMedia\Common\FS\Folder\Lister as FolderLister;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Tool_Index extends Controller
{
	/**
	*	Constructor.
	*	@access		public
	*	@param		string		$predicateClassName		Class Name of Predicate Class
	*	@return		void
	*/
	public function index( string $predicateClassName = "Alg_Validation_Predicates" )
	{
		$labels		= parse_ini_file( "config.ini", TRUE );
		$index		= FolderLister::getMixedList( "./tools/" );
		$list		= [];
		foreach( $index as $entry ){
			$fileName		= $entry->getFilename();
			if( $fileName == "index.php5" )
				continue;
			if( preg_match( "@^(catch|_|\.)@", $fileName ) )
				continue;
			$list[]	= $fileName;
		}
		$this->addData( 'labels', $labels );
		$this->addData( 'list', $list );
	}
}