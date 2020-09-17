<?php
class Controller_Admin_Mail_Template_Export extends CMF_Hydrogen_Controller
{
	/**
	 *	Export mail template as JSON.
	 *	Will provide file download by default.
	 *	@access		public
	 *	@param		integer		$templateId		ID of template to export
	 *	@return		void
	 */
	public function index( $templateId, $output = 'download' ){
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
		$json		= json_encode( $data, JSON_PRETTY_PRINT );
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
}
