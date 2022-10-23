<?php

use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\HydrogenFramework\View;

class View_Index extends View
{
	public function index()
	{
		$path	= $this->getData( 'path', FALSE );
		if( !empty( $path ) && file_exists( $path ) )
			return FileReader::load( $path );
	}
}
