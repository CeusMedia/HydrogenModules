<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

use BaconQrCode\Renderer\ImageRenderer as QrRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd as QrPngBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd as QrSvgBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle as QrStyle;
use BaconQrCode\Writer as QrWriter;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Share extends Logic
{
	protected Logic_FileBucket $logicFileBucket;

	protected Model_Share $modelShare;

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$relationId
	 *	@param		string		$path
	 *	@param		int			$access			Access type, one of Model_Share::ACCESS_* (public, captcha, login)
	 *	@param		$validity
	 *	@return		Entity_Share
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function create( string $moduleId, string $relationId, string $path, int $access, $validity ): Entity_Share
	{
		if( $this->get( $moduleId, $relationId ) )
			throw new RangeException( 'Share for module relation "'.$moduleId.':'.$relationId.'" is already existing' );
		$shareId	= $this->modelShare->add( Entity_Share::fromArray( [
			'status'		=> Model_Share::STATUS_ACTIVE,
			'access'		=> $access,
			'validity'		=> $validity,
			'moduleId'		=> $moduleId,
			'relationId'	=> $relationId,
			'path'			=> $path,
		] ) );
		/** @var Entity_Share $share */
		$share	= $this->modelShare->get( $shareId );
		$url	= $this->env->url.'share/'.$share->uuid;
		$this->generateQrCode( $share, $url );
		/** @var Entity_Share $share */
		$share	= $this->modelShare->get( $shareId );
		return $share;
	}

	/**
	 *	@param		string			$moduleId
	 *	@param		int|string		$relationId
	 *	@return		?Entity_Share
	 */
	public function get( string $moduleId, int|string $relationId ): ?Entity_Share
	{
		$indices	= [
			'moduleId'		=> $moduleId,
			'relationId'	=> $relationId,
		];
		/** @var ?Entity_Share $share */
		$share	= $this->modelShare->getByIndices( $indices );
		if( NULL !== $share ){
			$share->qr	= $this->logicFileBucket->getByPath( 'share-qr-'.$share->shareId );
			$filePath	= $this->logicFileBucket->getPath().$share->qr->hash;
			$share->qr->content	= base64_encode( file_get_contents( $filePath ) );
		}
		return $share;
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$relationId
	 *	@return		bool
	 */
	public function has( string $moduleId, string $relationId ): bool
	{
		$indices	= [
			'moduleId'		=> $moduleId,
			'relationId'	=> $relationId,
		];
		return (bool) $this->modelShare->getByIndices( $indices );
	}

	/**
	 *	@param		string		$uuid
	 *	@return		?Entity_Share
	 */
	public function getByUuid( string $uuid ): ?Entity_Share
	{
		/** @var ?Entity_Share $share */
		$share	= $this->modelShare->getByIndex( 'uuid', $uuid );
		if( NULL !== $share ){
			$share->qr	= $this->logicFileBucket->getByPath( 'share-qr-'.$share->shareId );
			$filePath	= $this->logicFileBucket->getPath().$share->qr->hash;
			$share->qr->content	= base64_encode( file_get_contents( $filePath ) );
		}
		return $share;
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$relationId
	 *	@param		string		$path
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function changePath( string $moduleId, string $relationId, string $path ): void
	{
		$share	= $this->get( $moduleId, $relationId );
		if( NULL !== $share )
			$this->modelShare->edit( $share->shareId, ['path' => $path] );
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$relationId
	 *	@return		bool
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $moduleId, string $relationId ): bool
	{
		$share	= $this->get( $moduleId, $relationId );
		return NULL !== $share && $this->modelShare->remove( $share->shareId );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logicFileBucket	= Logic_FileBucket::getInstance( $this->env );
		$this->modelShare		= new Model_Share( $this->env );
	}

	/**
	 *	@param		Entity_Share	$share
	 *	@param		string			$url
	 *	@return		Entity_File		File bucket object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generateQrCode( Entity_Share $share, string $url ): Entity_File
	{
//		return $this->generateQrCodeAsPng( $share, $url );
		return $this->generateQrCodeAsSvg( $share, $url );
	}

	/**
	 *	@param		Entity_Share	$share
	 *	@param		string			$url
	 *	@return		Entity_File		File bucket object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generateQrCodeAsPng( Entity_Share $share, string $url ): Entity_File
	{
		$fileName	= sys_get_temp_dir().'/qr-'.$share->shareId.'.png';
		$writer		= new QrWriter( new QrRenderer( new QrStyle( 128 ), new QrPngBackEnd() ) );
		$writer->writeFile( $url, $fileName );
		$file	= $this->logicFileBucket->add(
			$fileName,
			'share-qr-'.$share->shareId,
			'image/png',
			'Shares'
		);
		unlink( $fileName );
		return $this->logicFileBucket->get( $file );
	}

	/**
	 *	@param		Entity_Share	$share
	 *	@param		string			$url
	 *	@return		Entity_File		File bucket object
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generateQrCodeAsSvg( Entity_Share $share, string $url ): Entity_File
	{
		$fileName	= sys_get_temp_dir().'/qr-'.$share->shareId.'.svg';
		$writer		= new QrWriter( new QrRenderer( new QrStyle( 128 ), new QrSvgBackEnd() ) );
		$writer->writeFile( $url, $fileName );
		$file	= $this->logicFileBucket->add(
			$fileName,
			'share-qr-'.$share->shareId,
			'application/svg+xml',
			'Shares'
		);
		unlink( $fileName );
		return $this->logicFileBucket->get( $file );
	}
}
