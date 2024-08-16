<?php

use CeusMedia\Common\ADT\String_;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger;

class Controller_Admin_Mail_Template_Export extends Controller
{
	protected Messenger $messenger;
	protected Model_Mail_Template $modelTemplate;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		WebEnvironment	$env			Application Environment Object
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function __construct( WebEnvironment $env )
	{
		parent::__construct( $env, FALSE );
		$this->messenger			= $this->env->getMessenger();
		/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
		$this->modelTemplate		= $this->getModel( 'Mail_Template' );
	}

	/**
	 *	Export mail template as JSON.
	 *	Will provide file download by default.
	 *	@access		public
	 *	@param		string		$templateId		ID of template to export
	 *	@param		string		$output			Type of output (download|print)
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( string $templateId, string $output = 'download' ): void
	{
		$template	= $this->checkTemplate( $templateId );
		$title		= new String_( $template->title );
		$titleKey	= $title->hyphenate();										//  preserve whitespace in title as hyphen
		$json		= $this->generateJson( $templateId );
		$fileName	= vsprintf( '%s%s%s%s', [
			'MailTemplate_',													//  file name prefix @todo make configurable
			preg_replace( '/[: "\']/', '', $titleKey ),							//  template title as file ID (stripped invalid characters)
			'_'.date( 'Y-m-d' ),
			'.json',															//  file extension @todo make configurable
		] );
		switch( $output ){
			case 'print':
			case 'dev':
				remark( 'Title: '.$template->title );
				remark( 'File: '.$fileName );
				remark( 'JSON:' );
				xmp( $json );
				die;
			case 'download':
			default:
				HttpDownload::sendString( $json, $fileName );
		}
	}

	//  --  PROTECTED  --  //

	/**
	 *	@param		string		$templateId
	 *	@param		bool		$strict
	 *	@return		object|FALSE
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkTemplate( string $templateId, bool $strict = TRUE ): object|FALSE
	{
		$template	= $this->modelTemplate->get( $templateId );
		if( $template )
			return $template;
		if( $strict )
			throw new RangeException( 'Invalid template ID' );
		return FALSE;
	}

	/**
	 *	Generate JSON representing mail template.
	 *	@access		protected
	 *	@param		string		$templateId		ID of mail template
	 *	@return		string
	 *	@throws		RangeException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generateJson( string $templateId ): string
	{
		$template	= $this->checkTemplate( $templateId );
		$files		= ['styles' => [], 'images' => []];
		foreach( array_keys( $files ) as $topic ){
			if( $template->{$topic}[0] === '[' )
				$source	= json_decode( $template->$topic );
			else
				$source	= preg_split( '/\s*,\s*/', $template->$topic );

			foreach( $source as $item ){
				if( !file_exists( $item ) ){
					$this->messenger->noteError( 'File is missing: '.$item );
					continue;
				}
				$files[$topic][]	= [
					'filePath'	=> $item,
					'content'	=> base64_encode( FileReader::load( $item ) ),
				];
			}
		}

		$title		= new String_( $template->title );
		$titleKey	= $title->hyphenate();											//  preserve whitespace in title as hyphen

		$data	= [
			'type'		=> 'mail-template',
			'version'	=> 2,
			'entity'	=> [
				'title'		=> $template->title,
				'key'		=> $titleKey,
				'version'	=> '0',
				'language'	=> $template->language ?: '*',
				'contents'	=> [
					'text'		=> $template->plain,
					'html'		=> $template->html,
					'css'		=> $template->css,
				],
				'files'		=> [
					'styles'	=> $files['styles'],
					'images'	=> $files['images'],
				],
				'dates'		=> [
					'createdAt'		=> $template->createdAt,
					'modifiedAt'	=> $template->modifiedAt,
				]
			]
		];
		return json_encode( $data, JSON_PRETTY_PRINT );
	}
}
