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
		$this->setResult( Entity_Job_Result::STATUS_SUCCESS, $count, ['date'	=> $date] );
		$this->out( $date.' imported '.$count.' logged exceptions.' );							//  note sent mails
	}
}
