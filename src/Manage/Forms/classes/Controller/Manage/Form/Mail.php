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

	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			$data		= $this->request->getAll();
			$mailId	= $this->modelMail->add( $data, FALSE );
			$this->restart( 'edit/'.$mailId, TRUE );
		}
	}

	public function edit( $mailId ): void
	{
		$mail	= $this->checkId( $mailId );
		if( $this->request->has( 'save' ) ){
			$data	= $this->request->getAll();
			$this->modelMail->edit( $mailId, $data, FALSE );
			$this->restart( 'edit/'.$mailId, TRUE );
		}
		$this->addData( 'mail', $mail );
	}

	public function filter( $reset = NULL ): void
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

	public function index( $page = 0 ): void
	{
		$filters		= new Dictionary( array_merge(
			array_combine( $this->filters, array_fill( 0, count( $this->filters ), '' ) ),
			$this->session->getAll( $this->filterPrefix )
		) );

		$limit		= 15;
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
		$total		= $this->modelMail->count();
		$count		= $this->modelMail->count( $conditions );
		$mails		= $this->modelMail->getAll( $conditions, $orders, $limits );
		$this->addData( 'mails', $mails );
		$this->addData( 'page', $page );
		$this->addData( 'pages', ceil( $count / $limit ) );
		$this->addData( 'count', $count );
		$this->addData( 'total', $total );

		$this->addData( 'filters', $filters );

/*		$identifiers	= $this->modelMail->getAll(
			array(),
			array( 'identifier' => 'ASC' ),
			array(),
			array( 'identifier' )
		);
		$this->addData( 'identifiers', $identifiers );
*/
		$formats		= $this->modelMail->getAll(
			array(),
			array( 'format' => 'ASC' ),
			array(),
			array( 'format' )
		);
		$formats	= array_unique( $formats );
		$this->addData( 'formats', $formats );
	}

	public function remove( string $mailId ): void
	{
		$this->checkId( $mailId );
		$this->modelMail->remove( $mailId );
		$this->restart( NULL, TRUE );
	}

	public function view( string $mailId ): void
	{
		$mail	= $this->checkId( $mailId );
		$this->addData( 'mail', $mail );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->modelForm	= new Model_Form( $this->env );
		$this->modelMail	= new Model_Form_Mail( $this->env );
	}

	protected function checkId( int|string $mailId ): object
	{
		if( !$mailId )
			throw new RuntimeException( 'No mail ID given' );
		if( !( $mail = $this->modelMail->get( $mailId ) ) )
			throw new DomainException( 'Invalid mail ID given' );
		return $mail;
	}

	protected function checkIsPost()
	{
		if( !$this->request->getMethod()->isPost() )
			throw new RuntimeException( 'Access denied: POST requests, only' );
	}
}
