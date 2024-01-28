<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Catalog_Bookstore extends Controller
{
	/**	@var		Logic_Catalog_BookstoreManager		$logic */
	protected Logic_Catalog_BookstoreManager $logic;
	protected MessengerResource $messenger;
	protected Dictionary $request;
	protected Dictionary $session;

	public function index(){
		$this->restart( 'article', TRUE );
	}

	protected function __onInit(): void
	{
		$this->logic		= new Logic_Catalog_BookstoreManager( $this->env );
		$this->messenger	= $this->env->getMessenger();
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
	}
}
