<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage_Form_Mail extends Controller
{
	protected HttpRequest $request;

	protected Dictionary $session;

	protected Model_Form $modelForm;

	protected Model_Form_Mail $modelMail;

	protected string $filterPrefix		= 'filter_manage_form_mail_';

	protected array $filters			= [
		'mailId',
		'roleType',
		'identifier',
		'format',
		'title',
	];

	/**
	 *	@return		void
	 *	@throws 	\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$mailId	= $this->modelMail->add( $data, FALSE );
			$this->restart( 'edit/'.$mailId, TRUE );
		}
	}

	/**
	 *	@param		string		$mailId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( string $mailId ): void
	{
		$mail	= $this->checkId( $mailId );
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$this->modelMail->edit( $mailId, $data, FALSE );
			$this->restart( 'edit/'.$mailId, TRUE );
		}
		$this->addData( 'mail', $mail );
	}

	/**
	 *	@param		bool		$reset
	 *	@return		void
	 */
	public function filter( bool $reset = NULL ): void
	{
		if( $reset ){
			foreach( $this->filters as $filter )
				$this->session->remove( $this->filterPrefix.$filter );
		}
		foreach( $this->filters as $filter ){
			if( $this->request->has( $filter ) ){
				$value	= $this->request->get( $filter );
				$this->session->set( $this->filterPrefix.$filter, $value );
			}
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		integer		$page
	 *	@param		integer		$limit
	 *	@return		void
	 */
	public function index( int $page = 0, int $limit = 15 ): void
	{
		$filters		= new Dictionary( array_merge(
			array_combine( $this->filters, array_fill( 0, count( $this->filters ), '' ) ),
			$this->session->getAll( $this->filterPrefix )
		) );

		$conditions	= [];

		if( (int) $filters->get( 'mailId' ) )
		 	$conditions['mailId']		= (int) $filters->get( 'mailId' );
		if( strlen( trim( $filters->get( 'title' ) ) ) )
		 	$conditions['title']		= '%'.$filters->get( 'title' ).'%';
		if( strlen( trim( $filters->get( 'identifier' ) ) ) )
		 	$conditions['identifier']	= '%'.$filters->get( 'identifier' ).'%';
		if( $filters->get( 'format' ) )
		 	$conditions['format']		= $filters->get( 'format' );
		if( $filters->get( 'roleType' ) )
		 	$conditions['roleType']		= $filters->get( 'roleType' );

		$orders		= ['title' => 'ASC'];
		$limits		= [$page * $limit, $limit];
		$count		= $this->modelMail->count( $conditions );
		$this->addData( 'mails', $this->modelMail->getAll( $conditions, $orders, $limits ) );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $count / $limit ) );
		$this->addData( 'count', $count );
		$this->addData( 'total', $this->modelMail->countFast( []) );
		$this->addData( 'filters', $filters );

/*		$identifiers	= $this->modelMail->getAll(
			[],
			['identifier' => 'ASC'],
			[],
			['identifier']
		);
		$this->addData( 'identifiers', $identifiers );
*/
		$formats		= $this->modelMail->getAll(
			[],
			['format' => 'ASC'],
			[],
			['format']
		);
		$formats	= array_unique( $formats );
		$this->addData( 'formats', $formats );
	}

	/**
	 *	@param		string		$mailId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( string $mailId ): void
	{
		$this->checkId( $mailId );
		$this->modelMail->remove( $mailId );
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		string		$mailId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( string $mailId ): void
	{
		$this->addData( 'mail', $this->checkId( $mailId ) );
	}

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelMail	= new Model_Form_Mail( $this->env );
	}

	/**
	 *	@param		int|string		$mailId
	 *	@return		Entity_Form_Mail
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function checkId( int|string $mailId ): Entity_Form_Mail
	{
		if( '' === trim( (int) $mailId ) )
			throw new RuntimeException( 'No mail ID given' );

		/** @var ?Entity_Form_Mail $mail */
		$mail	= $this->modelMail->get( $mailId );
		if( NULL === $mail )
			throw new DomainException( 'Invalid mail ID given' );

		return $mail;
	}
}
