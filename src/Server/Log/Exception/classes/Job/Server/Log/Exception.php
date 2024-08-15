<?php
class Job_Server_Log_Exception extends Job_Abstract
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function import(): void
	{
		$logic		= $this->env->getLogic()->get( 'logException');
		$count		= $logic->importFromLogFile();
		$this->out( date( "Y-m-d H:i:s" ).' imported '.$count.' logged exceptions.' );							//  note sent mails
	}
}
