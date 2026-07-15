<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Alg\ID;
use CeusMedia\Common\XML\Converter as XmlConverter;
use CeusMedia\Common\XML\ElementReader as XmlElementReader;
use CeusMedia\HydrogenFramework\Environment;

class Model_Newsletter_Theme
{
	protected array $attributesAuthor		= [
		'name'			=> '',
		'email'			=> '',
		'company'		=> '',
		'link'			=> '',
		'github'		=> '',
		'twitter'		=> '',
	];

	protected array $attributesCopyright	= [
		'year'			=> '',
		'link'			=> '',
	];

	protected array $attributesTimestamp	= [
		'source'		=> '',
	];

	protected array $attributesDescription	= [
		'format'		=> 'markdown',
	];

	protected array $attributesLicense		= [
		'id'			=> '',
	];

	protected Environment $env;

	protected string $themePath;

	/**
	 *	@param		Environment		$env
	 *	@param		string			$themePath
	 */
	public function __construct( Environment $env, string $themePath )
	{
		$this->env			= $env;
		$this->themePath	= rtrim( $themePath, '/' ).'/';
	}

	/**
	 *	@param		string		$templateId
	 *	@param		array		$data
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function createFromTemplate( string $templateId, array $data ): void
	{
		$modelTemplate	= new Model_Newsletter_Template( $this->env );
		/** @var Entity_Newsletter_Template $template */
		$template		= $modelTemplate->get( $templateId );
		$data			= new Dictionary( array_merge( $template->toArray(), $data ) );

		$entity			= new Entity_Newsletter_Theme();
		$entity->mailTemplateId	= $template->mailTemplateId;
		$entity->title			= $data->get( 'title' );
		$entity->version		= $data->get( 'version' );
		$entity->created		= date( 'c', $data->get( 'createdAt' ) );
		$entity->modified		= date( 'c', $data->get( 'modifiedAt' ) );
		$entity->sender			= (object) [
			'address'	=> $data->get( 'senderAddress' ),
			'name'		=> $data->get( 'senderName' ),
		];
		$entity->imprint		= $data->get( 'imprint' );
		$entity->styles			= $data->get( 'styles' );
		$entity->author			= (object) [
			'name'		=> $data->get( 'authorName' ),
			'email'		=> $data->get( 'authorEmail' ),
			'company'	=> $data->get( 'authorCompany' ),
			'url'		=> $data->get( 'authorUrl' ),
		];
		$entity->license		= $data->get( 'license' );
		$entity->licenseUrl		= $data->get( 'licenseUrl' );
		$entity->description	= $data->get( 'description' );

		$themeKey		= strtolower( $data->get( 'title' ) );
		$themeKey		= preg_replace( '/[^a-z0-9 ]/', '', $themeKey );
		$themeKey		= str_replace( ' ', '_', $themeKey ).'_v'.$data->get( 'version' );
		$version		= 0;
		$folder			= $this->themePath.$themeKey;
		while( file_exists( $folder ) ){
			$version++;
			$folder		= $this->themePath.$themeKey.'_'.$version;
		}
		mkdir( $folder );
		$json	= json_encode( $entity, JSON_PRETTY_PRINT );
		file_put_contents( $folder.'/template.json', $json );
		file_put_contents( $folder.'/template.html', $data->get( 'html' ) );
		file_put_contents( $folder.'/template.txt', $data->get( 'plain' ) );
		file_put_contents( $folder.'/template.css', $data->get( 'style' ) );

		$this->createThumbnailFromTheme( 0 === $version ? $themeKey : $themeKey.'_'.$version );
	}

	/**
	 *	Tries to create a theme preview thumbnail file.
	 *	@param		string		$themeKey
	 *	@return		bool
	 */
	public function createThumbnailFromTheme( string $themeKey ): bool
	{
		$folder		= $this->themePath.$themeKey;
		$url		= $this->env->url.'work/newsletter/template/previewTheme/'.$themeKey;

//		$this->captureUrlViewUsingWebKit( $url, $folder.'/template.png' );
		$this->captureUrlViewUsingPhantom( $url, $folder.'/template.png' );
		return true;
	}

	/**
	 *	Tries to create a theme preview thumbnail file.
	 *	@param		Entity_Newsletter_Template	$template
	 *	@param		string						$themeKey
	 *	@return		bool
	 */
	public function createThumbnailFromTemplate( Entity_Newsletter_Template $template, string $themeKey ): bool
	{
		$folder	= $this->themePath.$themeKey;
		$pathJs	= $this->env->getConfig()->get( 'path.scripts' );
		$url	= $this->env->url.'work/newsletter/template/preview/html/'.$template->newsletterTemplateId;
		$error	= Resource_PhantomJS::getInstance( $this->env )
			->setDebug( 1 )
			->setScript( $pathJs.'phantomjs/screenshot.js' )
			->execute( $url, $folder.'/template.png' );
		if( $error )
			$this->env->getMessenger()->noteFailure( $error );
		return !$error;
	}

	/**
	 *	@param		string		$theme
	 *	@return		Entity_Newsletter_Theme
	 */
	public function get( string $theme ): Entity_Newsletter_Theme
	{
		return $this->getFromFolder( $theme );
	}

	/**
	 *	@return		Entity_Newsletter_Theme[]
	 *	@throws		UnexpectedValueException	if the path cannot be opened.
	 *	@throws		RuntimeException			if the path is an empty string.
	 */
	public function getAll(): array
	{
		$themes	= [];
		if( !file_exists( $this->themePath ) )
			return $themes;

		$index	= new DirectoryIterator( $this->themePath );
		foreach( $index as $entry ){
			if( $entry->isDot() || !$entry->isDir() )
				continue;
			$data	= $this->getFromFolder( $entry->getFilename() );
			$themes[$entry->getFilename()]	= $data;
		}
		ksort( $themes );
		$list	= [];
		foreach( $themes as $theme )
			$list[$theme->id]	= $theme;
		return $list;
	}

	/**
	 *	@param		string		$theme
	 *	@return		Entity_Newsletter_Theme
	 *	@throws		RangeException		if meta file is not existing
	 */
	public function getFromFolder( string $theme ): Entity_Newsletter_Theme
	{
		if( file_exists( $this->themePath.$theme.'/template.json' ) )
			return $this->getFromFolderJson( $theme );
		if( file_exists( $this->themePath.$theme.'/template.xml' ) )
			return $this->getFromFolderXml( $theme );
		throw new RangeException( 'Theme meta file "'.$theme.'" is not existing' );
	}

	/**
	 *	@param		int|string		$id
	 *	@return		?Entity_Newsletter_Theme
	 */
	public function getFromId( int|string $id ): ?Entity_Newsletter_Theme
	{
		$themes	= $this->getAll();
		return $themes[$id] ?? NULL;
	}

	//  --  PROTECTED  --  //

	protected function captureUrlViewUsingPhantom( string $url, string $targetFilePath ): bool
	{
		$pathJs	= $this->env->getConfig()->get( 'path.scripts' );
		$error	= Resource_PhantomJS::getInstance( $this->env )
			->setDebug( 1 )
			->setScript( $pathJs.'phantomjs/screenshot.js' )
			->execute( $url, $targetFilePath, ...array_values( [
				'width'		=> 1024,
				'height'	=> 768,
//				'zoom'		=> 0.5,
			] ) );
		if( is_string( $error ) )
			$this->env->getMessenger()->noteFailure( $error );
		return !$error;
	}

	protected function captureUrlViewUsingWebKit( string $url, string $targetFilePath ): bool
	{
		$params		= [
			'width'		=> 1024,
			'height'	=> 768,
//			'zoom'		=> 0.5,
			'quality'	=> 85,
		];
		array_walk( $params, function( &$value, $key ) {$value = '--'.$key.' '.$value;} );
		$command	= vsprintf( 'wkhtmltoimage %s %s %s', [
			join( ' ', $params ),
			escapeshellarg( $url ),
			escapeshellarg( $targetFilePath ),
		] );

		$output		= [];
		exec( $command, $output, $error );
		// @todo handle errors
//		print_m($error);
//		print_m($output);
		return TRUE;
	}

	/**
	 *	@param		string		$theme
	 *	@return		Entity_Newsletter_Theme
	 */
	protected function getFromFolderJson( string $theme ): Entity_Newsletter_Theme
	{
		$json	= file_get_contents( $this->themePath.$theme.'/template.json' );

		$data	= json_decode( $json );
		if( !isset( $data->id ) ){
			$data->id	= ID::uuid();
			$json		= json_encode( $data, JSON_PRETTY_PRINT );
			file_put_contents( $this->themePath.$theme.'/template.json', $json );
		}

		$entity	= new Entity_Newsletter_Theme();
		foreach( $data as $key => $value )
			$entity->$key	= $value;
		$entity->folder	= $theme;

		return $entity;
	}

	/**
	 *	@param		string		$theme
	 *	@return		Entity_Newsletter_Theme
	 *	@throws		Exception
	 */
	protected function getFromFolderXml( string $theme ): Entity_Newsletter_Theme
	{
		$xml	= XmlElementReader::readFile( $this->themePath.$theme.'/template.xml' );
		foreach( $xml->author as $author ){
			foreach( $this->attributesAuthor as $attributeName => $attributeDefault )
				if( !$author->hasAttribute( $attributeName ) )
					$author->setAttribute( $attributeName, $attributeDefault );
		}
		if( !$xml->copyright )
			$xml->addChild( 'copyright' );
		foreach( $this->attributesCopyright as $attributeName => $attributeDefault )
			if( !$xml->copyright->hasAttribute( $attributeName ) )
				$xml->copyright->setAttribute( $attributeName, $attributeDefault );

		if( !$xml->description )
			$xml->addChild( 'description' );
		foreach( $this->attributesDescription as $attributeName => $attributeDefault )
			if( !$xml->description->hasAttribute( $attributeName ) )
				$xml->description->setAttribute( $attributeName, $attributeDefault );

		if( !$xml->createdAt )
			$xml->addChild( 'created' );
		if( !$xml->modifiedAt )
			$xml->addChild( 'modified' );
		foreach( $this->attributesTimestamp as $attributeName => $attributeDefault )
			if( !$xml->created->hasAttribute( $attributeName ) )
				$xml->created->setAttribute( $attributeName, $attributeDefault );
		foreach( $this->attributesTimestamp as $attributeName => $attributeDefault )
			if( !$xml->modified->hasAttribute( $attributeName ) )
				$xml->modified->setAttribute( $attributeName, $attributeDefault );

		if( !$xml->license )
			$xml->addChild( 'license' );
		foreach( $this->attributesLicense as $attributeName => $attributeDefault )
			if( !$xml->license->hasAttribute( $attributeName ) )
				$xml->license->setAttribute( $attributeName, $attributeDefault );

		$xml->addChild( 'id', $theme );
		$entity	= new Entity_Newsletter_Theme();
		$object	= XmlConverter::toPlainObject( $xml );
		foreach( $object as $key => $value )
			$entity->$key	= $value;
		$entity->folder	= $theme;

		return $entity;
	}
}
