#!/usr/bin/php
<?php

use CeusMedia\Common\CLI\Output\Progress as ProgressOutput;
use CeusMedia\Common\FS\Folder\Editor as FolderEditor;
use CeusMedia\Common\FS\Folder\RegexFilter as RegexFolderFilter;

require_once '../vendor/autoload.php';

Tool::$verbose	= TRUE;
Tool::$dry		= TRUE;
Tool::$pathOld	= '../test/src/';
Tool::$pathNew	= '../test/';
Tool::main( $argv );

class Tool
{
	public static string $pathOld	= '../';

	public static string $pathNew	= '../src/';

	public static bool $verbose		= FALSE;

	public static bool $dry			= TRUE;

	public static function main( $argv ): void
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

	protected static function dispatch( string $action ): void
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
	public static string $pathOld	= '../';

	public static string $pathNew	= '../src/';

	public static bool $verbose		= FALSE;

	public static bool $dry			= FALSE;

	public static function testSyntax(): void
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


	public static function findFilesWhichNeedWork(): void
	{
		$files	= Tool_Utilities::scanFolder( '.' );
		$i		= 0;
		$list	= [];
		foreach( $files as $filePathAbsolute => $filePathShort ){
			if( preg_match( '/\.php5?$/', $filePathShort ) ){
				$content	= trim( FS_File_Reader::load( $filePathAbsolute ) );
				if( str_ends_with( $content, '?>' ) )
					$list[]	= $filePathShort;
			}
		}
		foreach( $list as $nr => $filePath ){
			echo ( $nr + 1 ).'. '.substr( $filePath, 0 ).PHP_EOL;
		}
	}

	public static function copyToNewStructure(): void
	{
		FolderEditor::createFolder( self::$pathNew );
		$folders	= new RegexFolderFilter( '.', '@^[A-Z]@', FALSE, TRUE, FALSE );
		foreach( $folders as $folder ){
			if( !file_exists( self::$pathNew.$folder->getFilename() ) ){
				if( self::$verbose )
					echo "- copy folder: ".$folder->getFilename().' ... ';
				try {
					FolderEditor::copyFolder( $folder->getPathname(), self::$pathNew.$folder->getFilename() );
					if( self::$verbose )
						echo "done".PHP_EOL;
				}
				catch( Throwable $t ){
					if( self::$verbose )
						echo "skipped: ".$t->getMessage().PHP_EOL;
				}
			}
		}
	}
}
class Tool_NewStructure
{
	public static string $pathOld	= '../';

	public static string $pathNew	= '../src/';

	public static bool $verbose		= FALSE;

	public static bool $dry			= FALSE;

	public static function removePhpVersionInClassFileName(): void
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

	public static function createLinksToOldStructure( bool $verbose = FALSE ): void
	{
		$folders	= new RegexFolderFilter( '.', '@^[A-Z]@', FALSE, TRUE, FALSE );
		foreach( $folders as $folder ){
			$folderName	= $folder->getFilename();
			if( $verbose ?? FALSE )
				echo "- link files in folder: ".$folderName.':'.PHP_EOL;
			if( !self::$dry )
				FolderEditor::createFolder( self::$pathOld.$folderName );
			$files = Tool_Utilities::scanFolder( self::$pathNew.$folderName );
			$nr = 0;
			foreach( $files as $filePathAbsolute => $filePathShort ){
				$path		= preg_replace( '@^(.+/)*([^/]+)$@', '\\1', $filePathShort );
				$wayBack	= '../';
				if( $path ){
					if( !self::$dry )
						FolderEditor::createFolder( self::$pathOld.$folderName.'/'.$path );
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
			if( $item->isDot() || str_starts_with( $fileName, '.' ) )
				continue;
			if( str_starts_with( $fileName, 'test' ) )
				continue;
			if( $item->isDir() && !str_contains( $fileName, 'vendor' ) )
				$list	= array_merge( $list, self::scanFolder( $path.'/'.$fileName, $baseFolderToRemove ) );
			else
				$list[$item->getPathname()]	= preg_replace( '@^'.preg_quote( $baseFolderToRemove, '@' ).'/@', '', $path.'/'.$fileName );
		}
		return $list;
	}
}
