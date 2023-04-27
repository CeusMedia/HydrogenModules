<?php
abstract class Mail_Forum_Abstract extends Mail_Abstract
{
	protected Model_Forum_Post $modelPost;

	protected Model_Forum_Thread $modelThread;

	protected Model_Forum_Topic $modelTopic;

	/**
	 *	@todo			render text, too
	 *	@throws	ReflectionException
	 */
	protected function generate(): self
	{
		$this->modelPost	= new Model_Forum_Post( $this->env );
		$this->modelThread	= new Model_Forum_Thread( $this->env );
		$this->modelTopic	= new Model_Forum_Topic( $this->env );

		$this->setHtml( $this->renderHtmlBody() );
		return $this;
	}

	abstract protected function renderHtmlBody(): string;

}
