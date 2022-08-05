#!/usr/bin/php
<?php

use CeusMedia\Common\CLI\Output\Progress as ProgressOutput;

require_once 'vendor/autoload.php';

Tool::$verbose	= TRUE;
Tool::$dry		= TRUE;
Tool::$pathOld	= 'test/src/';
Tool::$pathNew	= 'test/';
Tool::main( $argv );

class Tool
{
	public static $pathOld	= '';

	public static $pathNew	= 'src/';

	public static $verbose	= FALSE;

	public static $dry		= TRUE;

	public static function main( $argv )
	{
		$scriptName	= basename( __FILE__ );
		$arguments	= array_values( array_diff( $argv, [$scriptName, './'.$scriptName] ) );
		$action		= $arguments[0] ?? NULL;

		Tool_OldStructure::$pathNew	= self::$pathOld;
		Tool_OldStructure::$pathNew	= self::$pathNew;
		Tool_OldStructure::$verbose	= self::$verbose;
		Tool_OldStructure::$dry		= self::$dry;

		Tool_NewStructure::$pathOld	= self::$pathOld;
		Tool_NewStructure::$pathNew	= self::$pathNew;
		Tool_NewStructure::$verbose	= self::$verbose;
		Tool_NewStructure::$dry		= self::$dry;
		self::dispatch( $action );
	}

	protected static function dispatch( $action )
	{
		switch( $action ){
			case 'OldStructure::testSyntax':
				Tool_OldStructure::testSyntax();
				break;
			case 'OldStructure::findFilesWhichNeedWork':
				Tool_OldStructure::findFilesWhichNeedWork();
				break;
			case 'NewStructure::removePhpVersionInClassFileName':
				Tool_NewStructure::removePhpVersionInClassFileName();
				break;
			case 'OldStructure::copyToNewStructure':
				Tool_OldStructure::copyToNewStructure();
				break;
			case 'NewStructure::createLinksToOldStructure':
				Tool_NewStructure::createLinksToOldStructure();
				break;
			default:
				$lines	= [
					"Commands:",
					"- OldStructure::testSyntax",
					"- OldStructure::findFilesWhichNeedWork",
					"- OldStructure::copyToNewStructure",
					"- NewStructure::removePhpVersionInClassFileName",
					"- NewStructure::createLinksToOldStructure",
				];
				foreach( $lines as $line )
					echo $line.PHP_EOL;
		}
	}
}

class Tool_OldStructure
{
	public static $pathOld	= '';

	public static $pathNew	= 'src/';

	public static $verbose	= FALSE;

	public static $dry		= FALSE;

	public static function testSyntax()
	{
		$files		= Tool_Utilities::scanFolder( '.' );
		$phpFiles	= [];
		foreach( $files as $filePathAbsolute => $filePathShort ){
			if( preg_match( '/\.php5?$/', $filePathShort ) ){
				$phpFiles[$filePathAbsolute]	= $filePathShort;
			}
		}

		$nr			= 0;
		$list		= [];
		$progress	= new ProgressOutput();
		$progress->setTotal( count( $phpFiles ) );
		$progress->start();
		foreach( $phpFiles as $filePathAbsolute => $filePathShort ){
			$nr++;
			$return	= @exec( 'php -l '.$filePathAbsolute.' 2>&1 > /dev/null', $b, $code );
			if( $code != 0 )
				$list[]	= $return;
			$progress->update( $nr );
		}
		$progress->finish();
		foreach( $list as $item )
			echo '- '.$item.PHP_EOL;
	}


	public static function findFilesWhichNeedWork()
	{
		$files	= Tool_Utilities::scanFolder( '.' );
		$i		= 0;
		$list	= [];
		foreach( $files as $filePathAbsolute => $filePathShort ){
			if( preg_match( '/\.php5?$/', $filePathShort ) ){
				$content	= trim( FS_File_Reader::load( $filePathAbsolute ) );
				if( substr( $content, -2 ) === '?>' )
					$list[]	= $filePathShort;
			}
		}
		foreach( $list as $nr => $filePath ){
			echo ( $nr + 1 ).'. '.substr( $filePath, 0 ).PHP_EOL;
		}
	}

	public static function copyToNewStructure()
	{
		FS_Folder_Editor::createFolder( self::$pathNew );
		$folders	= new FS_Folder_RegexFilter( '.', '@^[A-Z]@', FALSE, TRUE, FALSE );
		foreach( $folders as $folder ){
			if( !file_exists( self::$pathNew.$folder->getFilename() ) ){
				if( $verbose )
					echo "- copy folder: ".$folder->getFilename().' ... ';
				try {
					FS_Folder_Editor::copyFolder( $folder->getPathname(), self::$pathNew.$folder->getFilename() );
					if( $verbose )
						echo "done".PHP_EOL;
				}
				catch( Throwable $t ){
					if( $verbose )
						echo "skipped: ".$t->getMessage().PHP_EOL;
				}
			}
		}
	}
}
class Tool_NewStructure
{
	public static $pathOld	= '';

	public static $pathNew	= 'src/';

	public static $verbose	= FALSE;

	public static $dry		= FALSE;

	public static function removePhpVersionInClassFileName()
	{
		$files	= Tool_Utilities::scanFolder( self::$pathNew );
		$list	= [];
		foreach( $files as $filePathAbsolute => $filePathShort ){
			if( preg_match( '@/classes/.+\.php5$@', $filePathShort ) ){
				if( self::$verbose )
					echo "- rename: ".$filePathShort.PHP_EOL;
				if( !self::$dry )
					rename( $filePathAbsolute, rtrim( $filePathAbsolute, '5' ) );
			}
		}
		echo "renamed ".count( $list )." file".PHP_EOL;
	}

	public static function createLinksToOldStructure()
	{
		$folders	= new FS_Folder_RegexFilter( '.', '@^[A-Z]@', FALSE, TRUE, FALSE );
		foreach( $folders as $folder ){
			$folderName	= $folder->getFilename();
			if( $verbose )
				echo "- link files in folder: ".$folderName.':'.PHP_EOL;
			if( !self::$dry )
				FS_Folder_Editor::createFolder( self::$pathOld.$folderName );
			$files = Tool_Utilities::scanFolder( self::$pathNew.$folderName );
			$nr = 0;
			foreach( $files as $filePathAbsolute => $filePathShort ){
				$path		= preg_replace( '@^(.+/)*([^/]+)$@', '\\1', $filePathShort );
				$wayBack	= '../';
				if( $path ){
					if( !self::$dry )
						FS_Folder_Editor::createFolder( self::$pathOld.$folderName.'/'.$path );
					$pathParts	= explode( '/', rtrim( $path, '/' ) );
					$wayBack	= str_repeat( '../', count( $pathParts ) + 1 );
				}
				if( !file_exists( self::$pathOld.$folderName.'/'.$filePathShort ) ){
					$filePathLink	= $filePathShort;
					if( preg_match( '@/classes/.+\.php$@', $filePathShort ) )
						$filePathLink	.= '5';
					$nr++;
					if( $verbose )
						echo "  - linking file ".$filePathShort.PHP_EOL;
					if( !self::$dry )
						symlink( $wayBack.'src/'.$folderName.'/'.$filePathShort, self::$pathOld.$folderName.'/'.$filePathLink );
				}
			}
		}
		echo "  - linked ".$nr." files".PHP_EOL;
	}
}

class Tool_Utilities
{
	public static function scanFolder( string $path, string $baseFolderToRemove = NULL ): array
	{
		$path	= rtrim( $path, '/' );
		$baseFolderToRemove	= $baseFolderToRemove ? $baseFolderToRemove : $path;
		$list	= [];
		$index	= new DirectoryIterator( $path );
		foreach( $index as $item ){
			$fileName	= $item->getFilename();
			if( $item->isDot() || substr( $fileName, 0, 1 ) === '.' )
				continue;
			if( preg_match( '@^test@', $fileName ) )
				continue;
			if( $item->isDir() && !preg_match( '/vendor/', $fileName ) )
				$list	= array_merge( $list, self::scanFolder( $path.'/'.$fileName, $baseFolderToRemove ) );
			else
				$list[$item->getPathname()]	= preg_replace( '@^'.preg_quote( $baseFolderToRemove, '@' ).'/@', '', $path.'/'.$fileName );
		}
		return $list;
	}
}
