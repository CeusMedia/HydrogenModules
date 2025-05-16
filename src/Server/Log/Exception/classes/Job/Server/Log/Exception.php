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
		$logic		= Logic_Log_Exception::getInstance( $this->env );
		$count		= $logic->importFromLogFile( 200, 'strictInlineStrategy' );
		$this->setResult( Entity_Job_Result::STATUS_SUCCESS, $count, ['date'	=> $date] );
		$this->out( $date.' imported '.$count.' logged exceptions.' );							//  note sent mails
	}
}
