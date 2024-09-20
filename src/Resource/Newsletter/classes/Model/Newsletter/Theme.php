<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\Alg\ID;
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
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function createFromTemplate( string $templateId, array $data ): void
	{
		$modelTemplate	= new Model_Newsletter_Template( $this->env );
		$template		= $modelTemplate->get( $templateId );
		$data			= (object) array_merge( (array) $template, $data );

		$entity			= new Entity_Newsletter_Theme();
		$entity->title		= $data->title;
		$entity->version	= $data->version;
		$entity->created	= date( 'c', $data->createdAt );
		$entity->modified	= date( 'c', $data->modifiedAt );
		$entity->sender		= (object) [
			'address'	=> $data->senderAddress,
			'name'		=> $data->senderName,
		];
		$entity->imprint	= $data->imprint;
		$entity->styles	= $data->styles;
		$entity->author		= (object) [
			'name'		=> $data->authorName,
			'email'		=> $data->authorEmail,
			'company'	=> $data->authorCompany,
			'url'		=> $data->authorUrl,
		];
		$entity->license	= $data->license;
		$entity->licenseUrl	= $data->licenseUrl;
		$entity->description	= $data->description;

		$themeKey		= strtolower( $data->title );
		$themeKey		= preg_replace( '/[^a-z0-9 ]/', '', $themeKey );
		$themeKey		= str_replace( ' ', '_', $themeKey ).'_v'.$data->version;
		$version		= 0;
		$folder			= $this->themePath.$themeKey;
		while( file_exists( $folder ) ){
			$version++;
			$folder		= $this->themePath.$themeKey.'_'.$version;
		}
		mkdir( $folder );
		$json	= json_encode( $entity, JSON_PRETTY_PRINT );
		file_put_contents( $folder.'/template.json', $json );
		file_put_contents( $folder.'/template.html', $data->html );
		file_put_contents( $folder.'/template.txt', $data->plain );
		file_put_contents( $folder.'/template.css', $data->style );

		$pathJs		= $this->env->getConfig()->get( 'path.scripts' );

		$error	= Resource_PhantomJS::getInstance( $this->env )->setDebug( 1 )->execute(
			$pathJs.'phantomjs/screenshot.js',
			$this->env->url.'work/newsletter/template/preview/html/'.$templateId,
			$folder.'/template.png'
		);
		if( $error )
			$this->env->getMessenger()->noteFailure( $error );
	}

	public function get( string $theme ): Entity_Newsletter_Theme
	{
		return $this->getFromFolder( $theme );
	}

	/**
	 * @return Entity_Newsletter_Theme[]
	 * @throws UnexpectedValueException if the path cannot be opened.
	 * @throws RuntimeException if the path is an empty string.
	 */
	public function getAll(): array
	{
		$themes	= [];
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
		$object	= \CeusMedia\Common\XML\Converter::toPlainObject( $xml );
		foreach( $object as $key => $value )
			$entity->$key	= $value;
		$entity->folder	= $theme;

		return $entity;
	}
}
