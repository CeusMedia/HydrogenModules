<?php
class Job_Server_Log_Exception extends Job_Abstract
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function import(): void
	{
		$date		= date( "Y-m-d H:i:s" );
		$logic		= $this->env->getLogic()->get( 'logException');
		$count		= $logic->importFromLogFile();
		$this->results	= (object) [
			'date'	=> $date,
			'count'	=> $count,
		];
		$this->out( $date.' imported '.$count.' logged exceptions.' );							//  note sent mails
	}
}
