<?php

use CeusMedia\HydrogenFramework\Environment;

class Controller_Admin_Mail_Template_Export extends CMF_Hydrogen_Controller
{
	protected $messenger;
	protected $modelTemplate;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env			Application Environment Object
	 *	@return		void
	 */
    public function __construct( Environment $env )
    {
		parent::__construct( $env, FALSE );
		$this->messenger			= $this->env->getMessenger();
		$this->modelTemplate		= $this->getModel( 'Mail_Template' );
	}

	/**
	 *	Export mail template as JSON.
	 *	Will provide file download by default.
	 *	@access		public
	 *	@param		string		$templateId		ID of template to export
	 *	@param		string		$output			Type of output (download|print)
	 *	@return		void
	 */
	public function index( string $templateId, string $output = 'download' )
	{
		$template	= $this->checkTemplate( $templateId );
		$title		= new ADT_String( $template->title );
		$titleKey	= $title->hyphenate();										//  preserve whitespace in title as hyphen
		$json		= $this->generateJson( $templateId );
		$fileName	= vsprintf( '%s%s%s%s', array(
			'MailTemplate_',													//  file name prefix @todo make configurable
			preg_replace( '/[: "\']/', '', $titleKey ),							//  template title as file ID (stripped invalid characters)
			'_'.date( 'Y-m-d' ),
			'.json',															//  file extension @todo make configurable
		) );
		switch( $output ){
			case 'print':
			case 'dev':
				remark( 'Ttile: '.$template->title );
				remark( 'File: '.$fileName );
				remark( 'JSON:' );
				xmp( $json );
				die;
			case 'download':
			default:
				Net_HTTP_Download::sendString( $json, $fileName, TRUE );
		}
	}

	//  --  PROTECTED  --  //

	protected function checkTemplate( $templateId, bool $strict = TRUE )
	{
		$template   = $this->modelTemplate->get( $templateId );
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
				$files[$topic][]	= array(
					'filePath'	=> $item,
					'content'	=> base64_encode( FS_File_Reader::load( $item ) ),
				);
			}
		}

		$title		= new ADT_String( $template->title );
		$titleKey	= $title->hyphenate();											//  preserve whitespace in title as hyphen

		$data	= array(
			'type'		=> 'mail-template',
			'version'	=> 2,
			'entity'	=> array(
				'title'		=> $template->title,
				'key'		=> $titleKey,
				'version'	=> '0',
				'language'	=> $template->language ? $template->language : '*',
				'contents'	=> array(
					'text'		=> $template->plain,
					'html'		=> $template->html,
					'css'		=> $template->css,
				),
				'files'		=> array(
					'styles'	=> $files['styles'],
					'images'	=> $files['images'],
				),
				'dates'		=> array(
					'createdAt'		=> $template->createdAt,
					'modifiedAt'	=> $template->modifiedAt,
				)
			)
		);
		return json_encode( $data, JSON_PRETTY_PRINT );
	}
}
