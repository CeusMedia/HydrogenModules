<?php
class Model_Newsletter_Theme{

	protected $attributesAuthor	= array(
		'name'			=> '',
		'email'			=> '',
		'company'		=> '',
		'link'			=> '',
		'github'		=> '',
		'twitter'		=> '',
	);
	protected $attributesCopyright	= array(
		'year'			=> '',
		'link'			=> '',
	);
	protected $attributesTimestamp	= array(
		'source'		=> '',
	);
	protected $attributesDescription	= array(
		'format'		=> 'markdown',
	);
	protected $attributesLicense	= array(
		'id'			=> '',
	);

	public function __construct( $env, $themePath ){
		$this->env			= $env;
		$this->themePath	= rtrim( $themePath, '/' ).'/';
	}

	public function createFromTemplate( $templateId, $data ){
		$modelTemplate	= new Model_Newsletter_Template( $this->env );
		$template		= $modelTemplate->get( $templateId );
		$data			= (object) array_merge( (array) $template, $data );
		$json	= (object) array(
			'id'		=> Alg_ID::uuid(),
			'title'		=> $data->title,
			'version'	=> $data->version,
			'created'	=> date( 'c', $data->createdAt ),
			'modified'	=> date( 'c', $data->modifiedAt ),
			'sender'	=> array(
				'address'	=> $data->senderAddress,
				'name'		=> $data->senderName,
			),
			'imprint'	=> $data->imprint,
			'styles'	=> $data->styles,
			'author'	=> array(
				'name'		=> $data->authorName,
				'email'		=> $data->authorEmail,
				'company'	=> $data->authorCompany,
				'url'		=> $data->authorUrl,
			),
			'license'		=> $data->license,
			'licenseUrl'	=> $data->licenseUrl,
			'description'	=> $data->description,
		);
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
		$json	= json_encode( $json, JSON_PRETTY_PRINT );
		file_put_contents( $folder.'/template.json', $json );
		file_put_contents( $folder.'/template.html', $data->html );
		file_put_contents( $folder.'/template.txt', $data->plain );
		file_put_contents( $folder.'/template.css', $data->style );

		$pathJs		= $this->env->getConfig()->get( 'path.scripts' );

		$error	= Resource_PhantomJS::getInstance( $this->env )->setDebug(1)->execute(
			$pathJs.'phantomjs/screenshot.js',
			$this->env->url.'work/newsletter/template/preview/html/'.$templateId,
			$folder.'/template.png'
		);
		if( $error )
			$this->env->getMessenger()->noteFailure( $error );
	}

	public function get( $theme ){
		return $this->getFromFolder( $theme );
	}

	public function getAll(){
		$themes	= array();
		$index	= new \DirectoryIterator( $this->themePath );
		foreach( $index as $entry ){
			if( $entry->isDot() || !$entry->isDir() )
				continue;
			$data	= $this->getFromFolder( $entry->getFilename() );
			$themes[$entry->getFilename()]	= $data;
		}
		ksort( $themes );
		$list	= array();
		foreach( $themes as $theme )
			$list[$theme->id]	= $theme;
		return $list;
	}

	public function getFromFolder( $theme ){
		if( file_exists( $this->themePath.$theme.'/template.json' ) )
			return $this->getFromFolderJson( $theme );
		if( file_exists( $this->themePath.$theme.'/template.xml' ) )
			return $this->getFromFolderXml( $theme );
		throw new \RangeException( 'Theme meta file "'.$theme.'" is not existing' );
	}

	protected function getFromFolderJson( $theme ){
		$json	= file_get_contents( $this->themePath.$theme.'/template.json' );
		$data	= json_decode( $json );
		if( !isset( $data->id ) ){
 			$data->id	= Alg_ID::uuid();
			$json		= json_encode( $data, JSON_PRETTY_PRINT );
			file_put_contents( $this->themePath.$theme.'/template.json', $json );
		}
		$data->folder	= $theme;
		return $data;
	}

	protected function getFromFolderXml( $theme ){
		$xml	= \XML_ElementReader::readFile( $this->themePath.$theme.'/template.xml' );
		foreach( $xml->author as $author ){
			foreach( $this->attributesAuthor as $attributeName => $attributeDefault )
				if( !$author->hasAttribute( $attributeName ) )
					$author->setAttribute( $attributeName, $attributeDefault );
		}
		if( !$xml->copyright )
			$xml->addChild( 'copyright', NULL );
		foreach( $this->attributesCopyright as $attributeName => $attributeDefault )
			if( !$xml->copyright->hasAttribute( $attributeName ) )
				$xml->copyright->setAttribute( $attributeName, $attributeDefault );

		if( !$xml->description )
			$xml->addChild( 'description', NULL );
		foreach( $this->attributesDescription as $attributeName => $attributeDefault )
			if( !$xml->description->hasAttribute( $attributeName ) )
				$xml->description->setAttribute( $attributeName, $attributeDefault );

		if( !$xml->createdAt )
			$xml->addChild( 'created', NULL );
		if( !$xml->modifiedAt )
			$xml->addChild( 'modified', NULL );
		foreach( $this->attributesTimestamp as $attributeName => $attributeDefault )
			if( !$xml->created->hasAttribute( $attributeName ) )
				$xml->created->setAttribute( $attributeName, $attributeDefault );
		foreach( $this->attributesTimestamp as $attributeName => $attributeDefault )
			if( !$xml->modified->hasAttribute( $attributeName ) )
				$xml->modified->setAttribute( $attributeName, $attributeDefault );

		if( !$xml->license )
			$xml->addChild( 'license', NULL );
		foreach( $this->attributesLicense as $attributeName => $attributeDefault )
			if( !$xml->license->hasAttribute( $attributeName ) )
				$xml->license->setAttribute( $attributeName, $attributeDefault );

		$xml->addChild( 'id', $theme );
		return $xml;
	}

	public function getFromId( $id ){
		$themes	= $this->getAll();
		return $themes[$id];
	}
}
?>