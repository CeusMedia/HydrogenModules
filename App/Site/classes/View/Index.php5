<?php
class View_Index extends CMF_Hydrogen_View{

	public function index(){
		if( $path = $this->getData( 'path', FALSE ) )
			if( file_exists( $path ) )
				return File_Reader::load( $path );
	}
}
?>
