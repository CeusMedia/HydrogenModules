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
		print_m( $labels );
		$index		= Folder_Lister::getMixedList( "./tools/" );
		$list		= array();
		foreach( $index as $entry )
		{
			$fileName		= $entry->getFilename();
			$description	= isset( $labels[$fileName]['description'] ) ? "<br/>".$labels[$fileName]['description'] : "";
			$tags			= "";
			if( isset( $labels[$fileName]['tags'] ) ){
				$tagList	= explode( ",", $labels[$fileName]['tags'] );
				foreach( $tagList as $id => $tag )
					$tagList[$id]	= '<span class="tag">'.$tag.'</span>';
				$tags	= implode( ", ", $tagList );
				$tags	= '<br/><span class="tags">Tags: '.$tags.'</span>';
			}
			if( $fileName == "index.php5" )
				continue;
			if( preg_match( "@^(catch|_|\.)@", $fileName ) )
				continue;
			$link	= UI_HTML_Tag::create( "a", $fileName, array( 'href' => "./".$fileName."/" ) );
			$list[$fileName]	= UI_HTML_Tag::create( "li", $link.$description.$tags );
		}
		$this->addData( 'list', $list );
	}
}
?>



