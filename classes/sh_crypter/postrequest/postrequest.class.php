<?php

/**
* Pateysoft Creation
* All rights reserved
* 2007-2008
* @author Ludovic PATEY
* @version 2.0
* @website http://www.pateysoft.fr/Envoyer-une-requete-POST-par-PHP.html
* @license BSD
*
* This is a class used to communicate with others serveurs trough POST.
*/

class PostRequest {

	private $url;
	private $cookies = array();
	private $meta = array();
	private $data = array();
	private $files = array();
	private $headers = array();
	private $boundary;

	public function __construct( $url ) {
		$this->url = $url;
		$this->boundary = md5( microtime() );
		
		$this->setHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
	}
	
	public function setCookies( $cookies ) {
		$this->setHeader( 'cookie', $this->buildCookies( $cookies ) );
		$this->cookies = $cookies;
	}
	
	public function setData( $name, $value ) {
		$this->data[ $name ] = $value;
	}
	
	public function setFile( $name, $path, $mime ) {
		$contentType = 'multipart/form-data, boundary=' . $this->boundary;
		$this->setHeader( 'Content-Type', $contentType );
		$this->files[ $name ] = array( 
			'path' => $path,
			'mime' => $mime );
	}
	
	public function getCookies() {
		return $this->cookies;
	}
	
	public function setHeader( $name, $value ) {
		$this->headers[ $name ] = $value;
	}
	
	
	public function send() {
	
		$headers = '';
		foreach( $this->headers as $name => $value ) {
			$headers .= $name . ': ' . $value . "\r\n";
		}
		
		if( $this->files ) {
	
			$content = $this->buildMultipartQuery();
		
		}
		else {
			$content = http_build_query( $this->data );
		}

		$headers.= 'Content-Length: ' . strlen( $content );
	
		$context = stream_context_create( 
			array( 'http' => array( 'user_agent' => 
			'Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.8.1) Gecko/20061010 Firefox/2.0',
									'method' => 'POST',
									'content' => $content,
									'header' => $headers ) ) );
									
									
		
		$fh = fopen( $this->url, 'r', false, $context );
		
		$this->meta = stream_get_meta_data( $fh );
	
		$cookies = array();
		
		foreach( $this->meta['wrapper_data'] as $data ) {
			if( preg_match( '/Set-Cookie: ([^=]+)=([^;]+)/', $data, $cookie ) ) {
				$cookies[ $cookie[1] ] = $cookie[2];
			}
		
		}
		
		$this->cookies = $cookies;
	
		$return = '';
		
		while( !feof( $fh ) ) {
			$return  .= fread( $fh, 1024 );
		}
		
		fclose( $fh );
		
		return $return;
	}
	
	private function buildMultipartQuery() {
	
		$content = '--' . $this->boundary . "\n";
		
		foreach( $this->data as $key => $value ) {
			$content .= 'content-disposition: form-data; name="' 
				. $key . '"' . "\n\n" . $value . "\n" . '--' . $this->boundary . "\n";
		}
		
		foreach( $this->files as $key => $file ) {
		
			$content .= 'content-disposition: form-data; name="' 
				. $key . '"; filename=" ' . basename($file['path']) . '"' . "\n";
			$content .= 'Content-Type: ' . $file['mime'] . "\n";
			$content .= 'Content-Transfer-Encoding: binary' . "\n\n";
			$content .= file_get_contents( $file['path'] );
			$content .= "\n" . '--' . $this->boundary . "\n";
		}
		
		return $content;
	}
	
	private function buildCookies( $cookies ) {
	
		$return = '';
		foreach( $cookies as $name => $value ) {
			$return .= ' ' . $name . '=' . $value . ';';
		}
		
		return trim($return);
	}
}

?>