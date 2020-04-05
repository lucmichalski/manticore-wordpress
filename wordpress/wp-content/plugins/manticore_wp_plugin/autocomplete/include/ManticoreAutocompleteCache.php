<?php

class ManticoreAutocompleteCache {
	protected $temp_dir = '/tmp';
	const EXTENSION = '.dat';

	public function __construct() {

		$this->temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'manticore_cache';

		if ( ! file_exists( $this->temp_dir ) ) {
			mkdir( $this->temp_dir . DIRECTORY_SEPARATOR, 0777, true );
		}
	}

	public function set_cache( $phrase, $cached_data ) {
		if ( ! empty( trim( $phrase ) ) && ! empty( $cached_data ) ) {

			$hash = md5( trim( $phrase ) );
			$path = $this->temp_dir . DIRECTORY_SEPARATOR .
			        $hash[0] . $hash[1] . DIRECTORY_SEPARATOR . $hash[2] . $hash[3];
			if ( ! file_exists( $path ) ) {
				mkdir( $path, 0777, true );
			}

			$handle = fopen( $path . DIRECTORY_SEPARATOR. $hash  . self::EXTENSION, "wb" );
			if ( flock( $handle, LOCK_EX | LOCK_NB ) ) {
				fwrite( $handle, json_encode( $cached_data, JSON_UNESCAPED_UNICODE ) );
				flock( $handle, LOCK_UN );
			}
			fclose( $handle );
		}
	}

	public function check_cache( $phrase ) {
		if ( ! empty( trim( $phrase ) ) ) {
			$hash = md5( trim( $phrase ) );

			$path = $this->temp_dir . DIRECTORY_SEPARATOR .
			        $hash[0] . $hash[1] . DIRECTORY_SEPARATOR . $hash[2] . $hash[3];

			$file = $path . DIRECTORY_SEPARATOR. $hash  . self::EXTENSION;
			if ( file_exists( $file ) ) {
				return json_decode( file_get_contents( $file ), false );
			}
		}

		return false;
	}

	public function clean_cache() {
		$this->clear_recursive($this->temp_dir);
	}

	private function clear_recursive($path)
	{
		if (file_exists($path) && is_dir($path)) {
			$dirHandle = opendir($path);
			while (false !== ($file = readdir($dirHandle))) {
				if ($file != '.' && $file != '..' )
				{
					$tmpPath = $path . '/' . $file;
					if (is_dir($tmpPath)) {  // если папка
						$this->clear_recursive($tmpPath);
					} else {
						if (file_exists($tmpPath)) {
							// удаляем файл
							unlink($tmpPath);
						}
					}
				}
			}
			closedir($dirHandle);
			// удаляем текущую папку
			if (file_exists($path)) {
				rmdir($path);
			}
		}
	}

	public function clear_obsolete_cache( $delay = 'day' ) {
		if ( $delay == 'day' ) {
			$delay = 1;
		} else {
			$delay = 7;
		}
		$command = 'find ' . $this->temp_dir . ' -type f -mtime +' . $delay . ' -name \'*' . self::EXTENSION . '\' -print';
		exec( $command, $output, $retval );
		if ( ! empty( $output ) ) {

			foreach ( $output as $file ) {
				if ( $file[0] == '/' ) {
					unlink( $file );
				}
			}
		}
	}


}