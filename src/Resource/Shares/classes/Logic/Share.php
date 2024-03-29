<?php

use CeusMedia\Common\Alg\ID;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Share extends Logic
{
	protected Logic_FileBucket $logicFileBucket;

	protected Model_Share $modelShare;

	public function create( string $moduleId, string $relationId, string $path, $access, $validity ): object
	{
		if( $this->get( $moduleId, $relationId ) )
			throw new RangeException( 'Share for module relation "'.$moduleId.':'.$relationId.'" is already existing' );
		$shareId	= $this->modelShare->add( [
			'status'		=> 1,
			'access'		=> $access,
			'validity'		=> $validity,
			'moduleId'		=> $moduleId,
			'relationId'	=> $relationId,
			'path'			=> $path,
			'uuid'			=> ID::uuid(),
			'createdAt'		=> time(),
		] );
		$share	= $this->modelShare->get( $shareId );
		$url	= $this->env->url.'share/'.$share->uuid;
		$this->generateQrCode( $shareId, $url );
		return $this->modelShare->get( $shareId );
	}

	public function get( $moduleId, $relationId )
	{
		$indices	= [
			'moduleId'		=> $moduleId,
			'relationId'	=> $relationId,
		];
		$share	= $this->modelShare->getByIndices( $indices );
		if( $share ){
			$share->qr	= $this->logicFileBucket->getByPath( 'share-qr-'.$share->shareId );
			$filePath	= $this->logicFileBucket->getPath().$share->qr->hash;
			$share->qr->content	= base64_encode( file_get_contents( $filePath ) );
		}
		return $share;
	}

	public function has( $moduleId, $relationId ): bool
	{
		$indices	= [
			'moduleId'		=> $moduleId,
			'relationId'	=> $relationId,
		];
		return (bool) $this->modelShare->getByIndices( $indices );
	}

	public function getByUuid( string $uuid ): ?object
	{
		$share	= $this->modelShare->getByIndex( 'uuid', $uuid );
		if( $share ){
			$share->qr	= $this->logicFileBucket->getByPath( 'share-qr-'.$share->shareId );
			$filePath	= $this->logicFileBucket->getPath().$share->qr->hash;
			$share->qr->content	= base64_encode( file_get_contents( $filePath ) );
		}
		return $share;
	}

	public function changePath( string $moduleId, string $relationId, string $path ): void
	{
		$share	= $this->get( $moduleId, $relationId );
		if( $share )
			$this->modelShare->edit( $share->shareId, ['path' => $path] );
	}

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
	 *	@param		string		$shareId
	 *	@param		string		$url
	 *	@return		string
	 */
	protected function generateQrCode( string $shareId, string $url ): string
	{
		$fileName	= sys_get_temp_dir().'/qr-'.$shareId.'.png';
		$renderer	= new \BaconQrCode\Renderer\Image\Png();
		$renderer->setHeight( 32 );
		$renderer->setWidth( 32 );
		$writer		= new \BaconQrCode\Writer( $renderer );
		$writer->writeFile( $url, $fileName );
		$file	= $this->logicFileBucket->add( $fileName, 'share-qr-'.$shareId, 'image/png', 'Shares' );
		unlink( $fileName );
		return $file;
	}
}
