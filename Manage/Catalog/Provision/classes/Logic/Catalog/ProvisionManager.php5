<?php
class Logic_Catalog_ProvisionManager extends CMF_Hydrogen_Logic{

	protected function __onInit(){
		$this->logicAuth		= Logic_Authentication::getInstance( $this->env );
		$this->logicMail		= Logic_Mail::getInstance( $this->env );
		$this->modelProduct		= new Model_Provision_Product( $this->env );
		$this->modelLicense		= new Model_Provision_Product_License( $this->env );
		$this->modelUserLicense	= new Model_Provision_User_License( $this->env );
		$this->modelUserKey		= new Model_Provision_User_License_Key( $this->env );
		$this->modelUser		= new Model_User( $this->env );
	}

	/**
	 *	@todo   		rework, send mails
	 */
/*	public function addUserLicense( $userId, $productLicenseId, $assignFirst = FALSE ){
		$productLicense	= $this->modelLicense->get( $productLicenseId );
		$data		= array(
			'userId'			=> $userId,
			'productLicenseId'	=> $productLicenseId,
			'productId'			=> $productLicense->productId,
			'status'			=> 0,
			'uid'				=> substr( Alg_ID::uuid(), -12 ),
			'duration'			=> $productLicense->duration,
			'users'				=> $productLicense->users,
			'price'				=> $productLicense->price,
			'duration'			=> $productLicense->duration,
			'createdAt'			=> time(),
		);
		$userLicenseId	= $this->modelUserLicense->add( $data );
		$userLicense	= $this->modelUserLicense->get( $userLicenseId );
		for( $i=0; $i<$productLicense->users; $i++ ){
			$data		= array(
				'userLicenseId'		=> $userLicenseId,
				'userId'			=> ( $assignFirst && $i === 0 ) ? $userId : 0,
				'productLicenseId'	=> $productLicenseId,
				'productId'			=> $productLicense->productId,
				'status'			=> ( $assignFirst && $i === 0 ) ? 1 : 0,
				'uid'				=> $userLicense->uid.'-'.str_pad( ( $i + 1), 4, '0', STR_PAD_LEFT ),
				'createdAt'			=> time(),
			);
			$userLicenseKeyId	= $this->modelUserKey->add( $data );
		}
		return $userLicenseId;
	}*/


	/**
	 *	@todo   		doc
	 */
/*	public function countUserLicensesByProductLicense( $productLicenseId ){
		return $this->modelUserLicense->countByIndex( 'productLicenseId', $productLicenseId );
	}*/

	/**
	 *	@todo   		rework
	 */
	public function getDurationInSeconds( $duration ){
		$number	= (int) preg_replace( "/^([0-9]+)/", "\\1", $duration );
		$unit	= preg_replace( "/^([0-9]+)([a-z]+)$/", "\\2", $duration );
		$oneDay	= 24 * 60 * 60;

		switch( $unit ){
			case 'd':
				$factor		= $oneDay;
				break;
			case 'w':
				$factor		= 7 * $oneDay;
				break;
			case 'm':
				$factor		= 30 * $oneDay;
				break;
			case 'a':
				$factor		= 365 * $oneDay;
				break;
		}
		return $number * $factor;
	}

	public function getProduct( $productId ){
		$product	= $this->modelProduct->get( $productId );
		if( !$product )
			throw new RangeException( 'Product ID '.$productId.' is not existing' );
		return $product;
	}

	public function getProductLicense( $productLicenseId = 0 ){
		$productLicense	= $this->modelLicense->get( $productLicenseId );
		if( !$productLicense )
			throw new RangeException( 'Product license ID '.$productLicenseId.' is not existing' );
		$productLicense->product	= $this->modelProduct->get( $productLicense->productId );
		return $productLicense;
	}

	public function getProductLicenses( $productId, $status = NULL ){
		$indices	= array( 'productId' => $productId );
		if( $status !== NULL )
			$indices['status']	= $status;
		$orders		= array( 'rank' => 'ASC', 'title' => 'ASC' );
		$productLicenses	= $this->modelLicense->getAll( $indices, $orders );
		foreach( $productLicenses as $nr => $productLicense )
			$productLicense->product	= $this->modelProduct->get( $productLicense->productId );
		return $productLicenses;
	}

	public function getProductLicenseUri( $productLicenseIdOrId = 0, $absolute = FALSE ){
		$productLicense	= $productLicenseOrId;
		if( is_int( $productLicenseOrId ) )
			$productLicense	= $this->getProductLicense( $productLicenseOrId );
		if( !is_object( $productLicense ) )
			throw new InvalidArgumentException( 'Given article data is invalid' );
		$uri	= vsprintf( 'catalog/provision/product/license/%2$d-%3$s', array(
			$productLicense->productId,
			$productLicense->productLicenseId,
			$this->getUriPart( $productLicense->title ),
		) );
		return $absolute ? $this->env->url.$uri : './'.$uri;
	}

	/**
	 *	@todo		kriss: code doc
	 */
	public function getUriPart( $label, $delimiter = "_" ){
		$label	= str_replace( array( 'ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß' ), array( 'ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', 'ss' ), $label );
		$label	= preg_replace( "/[^a-z0-9 ]/i", "", $label );
		$label	= preg_replace( "/ +/", $delimiter, $label );
		return $label;
	}

	public function getProducts( $status = NULL ){
		$indices	= array();
		if( $status !== NULL )
			$indices['status']	= $status;
		$orders		= array( 'rank' => 'ASC', 'title' => 'ASC' );
		$products	= $this->modelProduct->getAll( $indices, $orders );
		return $products;
	}
}
?>
