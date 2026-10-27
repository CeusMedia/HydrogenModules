<?php

use CeusMedia\Common\Exception\Conversion as ConversionException;
use CeusMedia\Common\Exception\Data\Missing as DataMissingException;
use CeusMedia\Common\Exception\MissingExtension as MissingExtensionException;
use CeusMedia\HydrogenFramework\Logic;
use CeusMedia\Mail\Message\Parser as MailMessageParser;

class Logic_Mail_Compressor extends Logic
{

	public static function canBzip(): bool
	{
		return function_exists( 'bzcompress' ) && function_exists( 'bzdecompress' );
	}

	public static function canGzip(): bool
	{
		return function_exists( 'gzdeflate' ) && function_exists( 'gzinflate' );
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$string			String to compress
	 *	@param		integer		$compression	Compression to apply
	 *	@return		string
	 *	@throws		MissingExtensionException	If extension for decompression (BZIP or GZIP) is not installed
	 */
	public static function compressString( string $string, int $compression = Model_Mail::COMPRESSION_NONE ): string
	{
		switch( $compression ){
			case Model_Mail::COMPRESSION_BZIP:
				if( !static::canBzip() )
					throw new MissingExtensionException( 'Missing extension for BZIP compression' );
				return bzcompress( $string );
			case Model_Mail::COMPRESSION_GZIP:
				if( !static::canGzip() )
					throw new MissingExtensionException( 'Missing extension for GZIP compression' );
				return gzdeflate( $string );
			case Model_Mail::COMPRESSION_BASE64:
				return base64_encode( $string );
			case Model_Mail::COMPRESSION_NONE:
			default:
				return $string;
		}
	}

	/**
	 *	Tries to compress mail object from serial.
	 *	This serial will be created from the mail object instance, by default.
	 *	If disabled, a set serial by before taken decompression is used.
	 *
	 *	The created raw output will replace the raw content within given mail object.
	 *	@access		public
	 *	@param		Entity_Mail		$mail			Mail object to compress object serial within
	 *	@param		boolean			$serialize		Flag: try to realize mail object from decompressed serial (default: yes)
	 *	@throws		DataMissingException			If no object instance is available
	 *	@throws		DataMissingException			If no object serial is available
	 */
	public static function compressMailObject( Entity_Mail $mail, bool $serialize = TRUE ): void
	{
		if( $serialize ){
			if( empty( $mail->objectInstance ) )
				throw new DataMissingException( 'Mail object has not been decompressed before, no mail object instance available' );
			$mail->objectSerial	= serialize( $mail->objectInstance );
		}
		if( empty( $mail->objectSerial ) )
			throw new DataMissingException( 'Mail object has not been serialized or decompressed before, no mail object serial available' );
		$mail->object	= static::compressString( $mail->objectSerial, $mail->compression );
	}

	/**
	 *	Tries to decompress raw mail object serial and recreated the serialized mail class object.
	 *
	 *	The recreated mail class object will replace the compressed serial within the given mail object.
	 *	Since decompression is applied, the identified compression is set to mail object, as well.
	 *
	 *	Will not apply decompression again on already mail object.
	 *
	 *	@access		public
	 *	@param		Entity_Mail		$mail			Mail object to decompress serial within
	 *	@param		boolean			$unserialize	Flag: try to realize mail object from decompressed serial (default: yes)
	 *	@param		boolean			$force			Flag: force detection and decompression (default: no)
	 *	@throws		DataMissingException			If no raw (compressed) mail object serial available
	 *	@throws		MissingExtensionException		If extension for decompression (BZIP or GZIP) is not installed
	 *	@throws		ConversionException				If decompression (using BZIP or GZIP) or decoding (Base64) failed
	 *	@throws		ConversionException				If deserialization failed
	 */
	public static function decompressMailObject( Entity_Mail $mail, bool $unserialize = TRUE, bool $force = FALSE ): void
	{
		if( is_object( $mail->objectInstance ) && $unserialize && !$force )
			return;

		if( empty( $mail->object ) )
			throw new DataMissingException( 'No raw (compressed) mail object serial available' );

		if( empty( $mail->objectSerial ) || $force ){
			$method				= static::detectUsedMailCompression( $mail, $force );
			$mail->objectSerial	= static::decompressString( $mail->object, $method );
		}

		try{
			$creation		= unserialize( $mail->objectSerial );
			$mail->objectInstance	= $creation;
		}
		catch( Throwable ){
			try{
				static::decompressMailRaw( $mail );
				$class	= new ReflectionClass( $mail->mailClass );
				/** @var Mail_Abstract $instanceWithoutConstruction */
				$instanceWithoutConstruction	= $class->newInstanceWithoutConstructor();
				$mail->objectInstance			= $instanceWithoutConstruction;
				$mail->objectInstance->mail		= MailMessageParser::getInstance()->parse( $mail->rawInflated );
				static::compressMailObject( $mail );
			}
			catch( Throwable $t ){
				throw new ConversionException( 'Deserialization failed: '.$t->getMessage(), 0, $t );
			}
		}
		if( !$mail->objectInstance )
			throw new ConversionException( 'Deserialization failed' );
	}

	/**
	 *	Tries to decompress the raw column of mail database item.
	 *	In force mode, a beforehand made copy of raw database column will be used.
	 *	@access		public
	 *	@param		Entity_Mail		$mail		...
	 *	@param		boolean		$force		Flag: force detection and decompression (default: no)
	 *	@return		boolean
	 *	@throws		MissingExtensionException	If extension for decompression (BZIP or GZIP) is not installed
	 *	@throws		ConversionException			If decompression (using BZIP or GZIP) or decoding (Base64) failed
	 */
	public static function decompressMailRaw( Entity_Mail $mail, bool $force = FALSE ): bool
	{
		if( NULL !== $mail->rawInflated && !$force )
			return TRUE;

		$method				= static::detectUsedMailCompression( $mail, $force );
		$mail->rawInflated	= static::decompressString( $mail->raw, $method );
		return TRUE;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$string			String to compress
	 *	@param		integer		$compression	Compression to apply
	 *	@return		string
	 *	@throws		MissingExtensionException	If extension for decompression (BZIP or GZIP) is not installed
	 *	@throws		ConversionException			If decompression (using BZIP or GZIP) or decoding (Base64) failed
	 */
	public static function decompressString( string $string, int $compression ): string
	{
		switch( $compression ){
			case Model_Mail::COMPRESSION_BZIP:
				if( !static::canBzip() )
					throw MissingExtensionException::create( 'Missing extension for BZIP compression' );
				$result	= bzdecompress( $string );
				if( is_int( $result ) )
					throw new ConversionException( 'Decompression (BZIP) failed' );
				return $result;
			case Model_Mail::COMPRESSION_GZIP:
				if( !static::canGzip() )
					throw new MissingExtensionException( 'Missing extension for GZIP compression' );
				$result	= gzinflate( $string );
				if( FALSE === $result )
					throw new ConversionException( 'Decompression (GZIP) failed' );
				return $result;
			case Model_Mail::COMPRESSION_BASE64:
				$result	= base64_decode( $string );
				if( FALSE === $result )
					throw new ConversionException( 'Decoding (Base64) failed' );
				return base64_decode( $string );
			case Model_Mail::COMPRESSION_NONE:
			default:
				return $string;
		}
	}

	/**
	 *	Detected compression used when mail object was stored.
	 *	Stores detected compression method in given mail object.
	 *	Returns detected compression as one of Model_Mail::COMPRESSION_*.
	 *	@access		public
	 *	@param		Entity_Mail		$mail		Mail item from database
	 *	@param		boolean			$force		Flag: re-detect (default: no)
	 *	@return		integer			Detected compression as of Model_Mail::COMPRESSION_*
	 *	@throws		DataMissingException		If no raw (compressed) mail object serial available
	 */
	public static function detectUsedMailCompression( Entity_Mail $mail, bool $force = FALSE ): int
	{
		if( !$mail->compression || $force ){
			if( empty( $mail->object ) )
				throw new DataMissingException( 'No raw (compressed) mail object serial available' );

			$mail->compression	= Model_Mail::COMPRESSION_BASE64;
			if( str_starts_with( $mail->object, "BZ" ) )										//  BZIP compression detected
				$mail->compression	= Model_Mail::COMPRESSION_BZIP;
			else if( str_starts_with( $mail->object, "GZ" ) )									//  GZIP compression detected
				$mail->compression	=  Model_Mail::COMPRESSION_GZIP;
		}
		return $mail->compression;
	}

	/**
	 *	...
	 *	@access		public
	 *	@return		integer
	 */
	public static function getRecommendedCompression(): int
	{
		if( static::canBzip() )
			return Model_Mail::COMPRESSION_BZIP;
		if( static::canGzip() )
			return Model_Mail::COMPRESSION_GZIP;
		return Model_Mail::COMPRESSION_BASE64;
	}
}