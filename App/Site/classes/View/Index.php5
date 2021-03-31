<?php
class View_Index extends CMF_Hydrogen_View
{
	public function index()
	{
		$path	= $this->getData( 'path', FALSE );
		if( !empty( $path ) && file_exists( $path ) )
			return FS_File_Reader::load( $path );
	}
}
