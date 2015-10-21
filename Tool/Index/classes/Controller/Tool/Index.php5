<?php
class Controller_Tool_Index extends CMF_Hydrogen_Controller{

	/**
	*      Constructor.
	*      @access         public
	*      @param          string          $predicateClassName             Class Name of Predicate Class
	*      @return         void
	*/
	public function index( $predicateClassName = "Alg_Validation_Predicates" )
	{
		$labels		= parse_ini_file( "config.ini", TRUE );
		$index		= FS_Folder_Lister::getMixedList( "./tools/" );
		$list		= array();
		foreach( $index as $entry )
		{
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
?>



