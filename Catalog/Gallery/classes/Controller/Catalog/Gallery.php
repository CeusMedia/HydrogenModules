<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Download as HttpDownload;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

class Controller_Catalog_Gallery extends Controller
{
	/**	@var	Logic_ShopBridge				$bridge */
	protected $bridge;

	/**	@var	integer							$bridgeId */
	protected $bridgeId							= 0;

	/**	@var	Logic_Catalog_Gallery			$logic */
	protected $logic;

	protected $messenger;

	/**	@var	Model_Catalog_Gallery_Category	$modelCategory */
	protected $modelCategory;

	/**	@var	Model_Catalog_Gallery_Image		$modelImage */
	protected $modelImage;

	public static function __onRenderServicePanels( Environment $env, $context, $module, $data = [] )
	{
		$arguments	= new Dictionary( $data );
		if( $orderId = $arguments->get( 'orderId' ) ){
			$view		= new View_Catalog_Gallery( $env );
			$helper		= new View_Helper_Shop_FinishPanel_CatalogGallery( $env );
			$helper->setOrderId( $orderId );
			$context->registerServicePanel(
				'CatalogGallery',
				$helper,
				2
			);
		}
	}

	public function category( $categoryId, $arg2 = NULL, $arg3 = NULL )
	{
//		$categoryId	= (int) $categoryId;
		$category	= $this->logic->getCategory( $categoryId );
		if( !$category ){
			$this->messenger->noteError( 'Invalid category ID "'.$categoryId.'".' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'category', $category );
		$this->addData( 'images', $this->logic->getCategoryImages( $categoryId ) );
		$this->addData( 'pathImages', $this->logic->pathImages );

	}

	public function downloadOrder( $orderId )
	{

		$logic	= new Logic_Shop( $this->env );
		$order	= $logic->getOrder( $orderId, TRUE );
		if( !$order ){
			$this->messenger->noteError( 'Invalid order ID.' );
			$this->restart( NULL, TRUE );
		}
		if( $order->status < 3 ){
			$this->messenger->noteError( 'Order not payed.' );
			$this->restart( NULL, TRUE );
		}
		$duration	= $this->env->getConfig()->get( 'module.catalog_gallery.download.duration' ) * 3600;
		if( $order->createdAt < ( time() - $duration ) ){
			$this->messenger->noteError( 'Download-Link is expired.' );
			$this->restart( NULL, TRUE );
		}
		$pathImages	= $this->logic->pathImages.'original/';

		$archive			= new ZipArchive();
		$archiveFileName	= tempnam( sys_get_temp_dir(), 'HydrogenArchive_' );
		$archive->open( $archiveFileName, ZipArchive::CREATE );
		foreach( $order->positions as $position ){
			$article	= $this->logic->getImage( $position->articleId );
			if( $article ){
				$category	= $this->logic->getCategory( $article->galleryCategoryId );
				if( $category ){
					$fileName	= $category->title.'/'.$article->filename;
					$archive->addFile( $pathImages.$fileName, $fileName );
					$logic->setOrderPositionStatus( $position->positionId, Model_Shop_Order_Position::STATUS_DELIVERED );
				}
			}
		}
		$archive->close();
		HttpDownload::sendFile( $archiveFileName, 'Bestellung_'.$orderId.'.zip', FALSE );
		unlink( $archiveFileName );
		$logic->setOrderStatus( $orderId, Model_Shop_Order::STATUS_DELIVERED );				//  set order status to 'delivered'
		exit;
	}

	public function image( $imageId = NULL, $arg2 = NULL, $arg3 = NULL )
	{
		$imageId	= (int) $imageId;
		$image	= $this->logic->getImage( $imageId );
		if( !$image ){
			$this->messenger->noteError( 'Invalid image ID "'.$imageId.'".' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'image', $image );
		$this->addData( 'tax', $this->env->getConfig()->get( 'module.catalog_gallery.tax.rate' ) );
		$this->addData( 'category', $this->logic->getCategory( $image->galleryCategoryId ) );
		$this->addData( 'images', $this->logic->getCategoryImages( $image->galleryCategoryId ) );
	}

	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL )
	{
	}

	public function order( $imageId )
	{
		$image	= $this->modelImage->get( $imageId );
		if( !$image ){
			$this->messenger->noteError( 'Invalid image ID "'.$imageId.'".' );
			$this->restart( NULL, TRUE );
		}
		$forwardUrl	= urlencode( $this->logic->pathModule.'category/'.$image->galleryCategoryId );
		$restartUrl	= 'shop/addArticle/'.$this->bridgeId.'/'.$imageId.'/1?forwardTo='.$forwardUrl;
		$this->restart( $restartUrl );
	}

	protected function __onInit()
	{
		$this->messenger		= $this->env->getMessenger();

		$this->logic			= new Logic_Catalog_Gallery( $this->env );
		$this->modelCategory	= new Model_Catalog_Gallery_Category( $this->env );
		$this->modelImage		= new Model_Catalog_Gallery_Image( $this->env );
		if( $this->env->getModules()->has( 'Shop' ) ){
			$this->bridge			= new Logic_ShopBridge( $this->env );
			$this->bridgeId			= $this->bridge->getBridgeId( 'CatalogGallery' );
		}
//		$this->logic->cache->flush();
		$this->categories	= $this->logic->cache->get( 'catalog.gallery.categories' );
		if( !$this->categories ){
			$this->categories		= $this->modelCategory->getAll( ['parentId' => 0], ['rank' => 'ASC'] );
			foreach( $this->categories as $nr => $category )
				$this->categories[$nr]->images	= $this->logic->getCategoryImages( $category->galleryCategoryId );
			$this->logic->cache->set( 'catalog.gallery.categories', $this->categories );
		}
//		print_m( $this->logic->cache->index() );
//		die;

		$this->addData( 'categories', $this->categories );
		$this->addData( 'pathModule', $this->logic->pathModule );
		$this->addData( 'pathImages', $this->logic->pathImages );
		$this->addData( 'bridgeId', $this->bridgeId );
	}
}
