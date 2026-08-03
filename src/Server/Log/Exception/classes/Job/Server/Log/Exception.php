<?php
class Job_Server_Log_Exception extends Job_Abstract
{
	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function import(): void
	{
		$limits		= $this->getLimitsFromRequest();
		$date		= date( "Y-m-d H:i:s" );
		$logic		= Logic_Log_Exception::getInstance( $this->env );
		$count		= $logic->importFromLogFile( $limits[1], 'strictInlineStrategy' );
		$status		= match( $count ){
			0		=> Entity_Job_Result::STATUS_PARTIAL,		//  nothing imported
			default	=> Entity_Job_Result::STATUS_SUCCESS,		//  something imported
		};
		$this->setResult( $status, $count, ['date'	=> $date] );
		$this->out( $date.' imported '.$count.' logged exceptions.' );							//  note sent mails
	}
}
