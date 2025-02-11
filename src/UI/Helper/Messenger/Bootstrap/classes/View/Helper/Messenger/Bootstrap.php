<?php

use CeusMedia\Common\Alg\Time\Converter as TimeConverter;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Messenger_Bootstrap
{
	protected Environment $env;

	protected array $classes	= [
		'0'	=> 'messenger messenger-failure alert alert-danger bs4-alert-dark',
		'1'	=> 'messenger messenger-error alert alert-danger',
		'2'	=> 'messenger messenger-notice alert alert-info',
		'3'	=> 'messenger messenger-success alert alert-success',
	];

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	@param		?string		$timeFormat
	 *	@param		bool		$linkResources
	 *	@return		string
	 */
	public function render( string $timeFormat = NULL, bool $linkResources = FALSE ): string
	{
		$messages	= $this->env->getMessenger()->getMessages();
		if( !$messages )
			return '';

		$list	= [];
		foreach( $messages as $nr => $message ){
			$message	= (object) $message;
			if( $linkResources )
				$message->message	= preg_replace(
					'/(http.+)("|\'| )/U',
					'<a href="\\1">\\1</a>\\2',
					$message->message
				);

			$class	= $this->classes[$message->type].' messenger-message-'.$nr;
			$message	= HtmlTag::create( 'div', (string) $message->message, ['class' => 'messenger-message'] );
			if( $timeFormat && !empty( $message->timestamp ) ){
				$time		= TimeConverter::convertToHuman( $message->timestamp, $timeFormat );
				$time		= HtmlTag::create( 'span',  '['.$time.'] ', ['class' => 'time'] );
				$message	= $time.$message;
			}
			$buttonDismiss	= '';
			if( $this->env->getModules()->has( 'UI_JS_Messenger' ) ){
				$buttonClose	= HtmlTag::create( 'button', "&times;", [
					'type'		=> 'button',
					'onclick'	=> 'UI.Messenger.discardMessage($(this).parent());',
					'class'		=> 'close',
				] );
				$message		= $buttonClose.$message;
			}
			else{
				$buttonClose	= HtmlTag::create( 'button', "&times;", [
					'type'		=> 'button',
					'onclick'	=> '$(this).parent().slideUp();',
					'class'		=> 'close',
				] );
				$message		= $buttonClose.$message;
			}
			$list[] 	= HtmlTag::create( 'div', $message, ['class' => $class] );
		}
		$this->env->getMessenger()->clear();
		return HtmlTag::create( 'div', $list, ['class' => 'messenger-messages messenger-bootstrap'] );
	}

	/**
	 *	@param		Environment		$env
	 *	@param		?string			$timeFormat
	 *	@param		bool			$linkResources
	 *	@return		string
	 */
	public static function renderStatic( Environment $env, string $timeFormat = NULL, bool $linkResources = FALSE ): string
	{
		if( !$env->getMessenger()->getMessages() )
			return '';
		$helper		= new self( $env );
		return $helper->render( $timeFormat, $linkResources );
	}
}
