<?php
use CeusMedia\HydrogenFramework\Controller;

class Controller_Work_Newsletter_Link extends Controller
{
	protected Model_Newsletter_Link $modelLink;
	protected Model_Newsletter_Reader_Letter_Link $modelRelation;

	public function index(): void
	{
		$offset	= 0;
		$limit	= 10;
		$links	= $this->modelLink->getAll( [], ['url' => 'ASC'], [$offset, $limit] );
		foreach( $links as $link ){
			$link->clicks	= $this->modelRelation->countByIndices( [
				'newsletterLinkId'	=> $link->newsletterLinkId,
			] );
		}
		$this->addData( 'links', $links );
	}

	public function view( int|string $linkId ): void
	{
		$link	= $this->modelLink->get( $linkId );
		if( NULL === $link ){
			$this->env->getMessenger()->noteError( 'Invalid link ID' );
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'link', $link );
	}

	protected function __onInit(): void
	{
		$this->modelLink		= Model_Newsletter_Link::getInstance( $this->env );
		$this->modelRelation	= Model_Newsletter_Reader_Letter_Link::getInstance( $this->env );
	}
}
