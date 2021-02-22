<?php
abstract class Mail_Forum_Abstract extends Mail_Abstract
{
	protected $modelPost;

	protected $modelThread;

	protected $modelTopic;

	/**
	 *	@todo			render text, too
	 */
	protected function generate( $data = array() )
	{
		$this->modelPost	= new Model_Forum_Post( $this->env );
		$this->modelThread	= new Model_Forum_Thread( $this->env );
		$this->modelTopic	= new Model_Forum_Topic( $this->env );

		$html		= $this->renderBody( $data );
		$this->setHtml( $html );
	}
}
