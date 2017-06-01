<?php
class View_Helper_Mail_View_Source{

	protected $env;
	protected $mail;
	protected $logicMail;
	protected $mode			= 0;

	const MODE_NORMAL		= 0;
	const MODE_CONDENSED	= 1;

	public function __construct( $env ){
		$this->env			= $env;
		$this->logicMail	= new Logic_Mail( $this->env );
	}

	public function render(){
		if( !$this->mail )
			throw new RuntimeException( 'No mail object or ID set' );
		if( $this->mail->object->mail instanceof \CeusMedia\Mail\Message )
			$code	= \CeusMedia\Mail\Renderer::render( $this->mail->object->mail );
		else if( $this->mail->object->mail instanceof Net_Mail )
			$code	= $this->mail->object->mail->getBody();
		else
			throw new RangeException( 'No source renderer for mail object available' );
		switch( $this->mode ){
			case self::MODE_CONDENSED:
				$code	= $this->shortenMailCode( $code );
				break;
			case self::MODE_NORMAL:
				break;
			default:
				throw new RangeException( 'Invalid render mode' );
		}
		return $code;
	}

	public function setMail( $mailObjectOrId ){
		if( is_int( $mailObjectOrId ) )
			$mailObjectOrId	= $this->logicMail->getMail( $mailObjectOrId );
		if( !is_object( $mailObjectOrId ) )
			throw new InvalidArgumentException( 'Argument must be integer or object' );
		$this->mail	= $mailObjectOrId;
	}

	public function setMode( $mode ){
		if( !in_array( $mode, array( self::MODE_NORMAL, self::MODE_CONDENSED ) ) )
			throw new RangeException( 'Invalid mode' );
		$this->mode	= $mode;
	}

	protected function shortenMailCode( $code ){
		$status	= 0;
		$list	= array();
		foreach( explode( PHP_EOL, $code ) as $nr => $line ){
			$isEmpty	= !strlen( trim( $line ) );
			$isBased	= preg_match( '/^[\S]{75,78}$/', trim( $line ) );
			if( !$isEmpty && !$isBased ){
				if( $status === 3 ){
					$status	= 0;
					continue;
				}
				$status	= 0;
			}
			else if( $isEmpty )
				$status	= 1;
			else if( $status === 1 && $isBased )
				$status++;
			else if( $status === 2 && $isBased ){
				$list[count( $list ) - 1]	= '[data encoded with base64]';
				$status++;
			}
			if( $status === 3 )
				continue;
			$list[]	= $line;
		}
		return implode( PHP_EOL, $list );
	}
}
?>
