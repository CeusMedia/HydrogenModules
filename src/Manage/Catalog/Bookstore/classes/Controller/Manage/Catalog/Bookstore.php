<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Manage_Catalog_Bookstore extends Controller
{
	/**	@var		Logic_Catalog_BookstoreManager		$logic */
	protected Logic_Catalog_BookstoreManager $logic;

	protected Logic_Frontend $frontend;

	protected MessengerResource $messenger;

	protected HttpRequest $request;

	protected Dictionary $session;

	protected Dictionary $moduleConfig;

	public function index(): void
	{
		$this->restart( 'article', TRUE );
	}

	protected function __onInit(): void
	{
		$this->messenger		= $this->env->getMessenger();
		$this->request			= $this->env->getRequest();
		$this->session			= $this->env->getSession();
		$this->moduleConfig		= $this->env->getConfig()->getAll( 'module.manage_catalog_bookstore.', TRUE );
		$this->logic			= new Logic_Catalog_BookstoreManager( $this->env );
		$this->frontend			= Logic_Frontend::getInstance( $this->env );
		$this->addData( 'frontend', $this->frontend );
		$this->addData( 'moduleConfig', $this->moduleConfig );
	}
}
