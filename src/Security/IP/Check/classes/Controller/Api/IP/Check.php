<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Controller\Api as ApiController;

/**
 * API Controller for IP Check (IPv4 and IPv6).
 *
 * @category    Library
 * @package     CeusMedia.HydrogenModules
 * @author      Christian Würker <christian.wuerker@ceusmedia.de>
 * @copyright   2010-2026 Christian Würker (ceusmedia.de)
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 * @link        https://github.com/CeusMedia/HydrogenModules
 */
class Controller_Api_IP_Check extends ApiController
{
	protected Logic_IP_Check $logic;

	/**
	 * Check an IP address (IPv4 or IPv6) against blocklists.
	 * Expects the IP as a query parameter: ?ip=1.2.3.4 or ?ip=2001:db8::1
	 *
	 * @return void
	 * @throws JsonException
	 * @throws ReflectionException
	 */
	public function index(): void
	{
		$ip	= $this->request->get( 'ip', '' );
		if( '' === $ip ){
			$this->respondError( 'Missing "ip" parameter', 400 );
			return;
		}

		// Validate IP format (IPv4 or IPv6)
		if( FALSE === filter_var( $ip, FILTER_VALIDATE_IP ) ){
			$this->respondError( 'Invalid IP address', 400 );
			return;
		}

		try{
			$result	= $this->logic->checkIp( $ip );

			// Log rejected IPs to database
			if( empty( $result['clean'] ) ){
				$postData	= NULL;
				$headers	= NULL;

				// Collect POST data if available
				$request	= $this->env->getRequest();
				$method	= (string) $request->getMethod();
				if( 'POST' === $method || 'PUT' === $method || 'PATCH' === $method ){
					$body	= $request->getRawPostData();
					if( '' !== $body ){
						$decoded	= json_decode( $body, TRUE );
						$postData	= is_array( $decoded ) ? $decoded : [ 'raw' => $body ];
					}
				}

				// Collect relevant headers
				$headers	= [
					'user_agent'		=> (string) $request->get( 'HTTP_USER_AGENT', '' ),
					'accept_language'	=> (string) $request->get( 'HTTP_ACCEPT_LANGUAGE', '' ),
					'referer'			=> (string) $request->get( 'HTTP_REFERER', '' ),
				];

				$this->logic->logRejectedIp(
					$ip,
					$result,
					$method,
					(string) $request->getUrl( FALSE ),
					$postData,
					$headers
				);
			}

			$this->respondData( $result );
		}
		catch( \Throwable $e ){
			$this->env->getLog()->logException( $e, 'IP Check failed: ' . $e->getMessage() );
			$this->respondError( 'Internal server error', 500 );
		}
	}

	/**
	 * @return void
	 * @throws ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic = Logic_IP_Check::getInstance( $this->env );
		$this->sendAllowOriginHeaders();
	}

	protected function sendAllowOriginHeaders(): void
	{
		$origin	= $this->env->getConfig()->get( 'module.manage_forms.origin' );
		$origin	= $origin ?: $this->env->getBaseUrl();
		$origin	= rtrim( $origin, '/' );

		// Validate origin to prevent CRLF injection — must be a valid URI without control characters
		if( preg_match( '/[\r\n\t]/', $origin ) ){
			return;
		}
		header( 'Access-Control-Allow-Origin: ' . $origin );
		header( 'Access-Control-Allow-Credentials: true' );
	}
}
