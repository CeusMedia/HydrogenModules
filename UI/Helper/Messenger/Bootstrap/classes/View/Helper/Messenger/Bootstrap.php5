<?php
class View_Helper_Messenger_Bootstrap{

	protected $classes	= array(
		'0'	=> 'messenger messenger-failure alert alert-error',
		'1'	=> 'messenger messenger-error alert alert-error',
		'2'	=> 'messenger messenger-notice alert alert-info',
		'3'	=> 'messenger messenger-success alert alert-success',
	);

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
	}

	public function render( $timeFormat = NULL, $linkResources = FALSE ){
		$messages	= $this->env->getMessenger()->getMessages();
		$list		= "";
		if( $messages ){
			$list	= array();
			foreach( $messages as $nr => $message ){
				$message	= (object) $message;
				if( $linkResources )
					$message->message	= preg_replace(
						'/(http.+)("|\'| )/U',
						'<a href="\\1">\\1</a>\\2',
						$message->message
					);

				$class	= $this->classes[$message->type].' messenger-message-'.$nr;
				$message	= UI_HTML_Tag::create( 'div', $message->message, array( 'class' => 'messenger-message' ) );
				if( $timeFormat && !empty( $message->timestamp ) ){
					$time		= Alg_Time_Converter::convertToHuman( $message->timestamp, $timeFormat );
					$time		= UI_HTML_Tag::create( 'span',  '['.$time.'] ', array( 'class' => 'time' ) );
					$message	= $time.$message;
				}
				$buttonDismiss	= '';
				if( $this->env->getModules()->has( 'UI_JS_Messenger' ) ){
					$buttonClose	= UI_HTML_Tag::create( 'button', "&times;", array(
						'type'		=> 'button',
						'onclick'	=> 'UI.Messenger.discardMessage($(this).parent());',
						'class'		=> 'close',
					) );
					$message		= $buttonClose.$message;
				}
				else{
					$buttonClose	= UI_HTML_Tag::create( 'button', "&times;", array(
						'type'		=> 'button',
						'onclick'	=> '$(this).parent().slideUp();',
						'class'		=> 'close',
					) );
					$message		= $buttonClose.$message;
				}
				$list[] 	= UI_HTML_Tag::create( 'div', $message, array( 'class' => $class ) );
			}
			$list	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'messenger-messages messenger-bootstrap' ) );
		}
		$this->env->getMessenger()->clear();
		return $list;
	}

	public static function renderStatic( CMF_Hydrogen_Environment_Abstract $env, $timeFormat = NULL, $linkResources = FALSE ){
		if( !$env->getMessenger()->getMessages() )
			return;
		$helper		= new self( $env );
		return $helper->render( $timeFormat, $linkResources );
	}
}
?>
