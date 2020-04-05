<?php

class Manticore_Container {

	public $config;
	public $sphinxQL;
	public $service;
	public $backend;
	public $frontend;
	public $indexer;

	public function __construct() {
		$this->config = new Manticore_Config();
		if ( $this->config->admin_options['manticore_use_http'] == 'true' ) {
			$this->sphinxQL = new ManticoreHttpApi( $this->config );
		} else {
			$this->sphinxQL = new SphinxQL( $this->config );
		}

		$this->service  = new Manticore_Service( $this->config );
		$this->backend  = new Manticore_Backend( $this->config );
		$this->frontend = new Manticore_FrontEnd( $this->config );
		$this->indexer  = new Manticore_Indexing( $this->config );
		$this->api      = new Manticore_Api( $this->config );


		$this->config->attach( $this->sphinxQL );
		$this->config->attach( $this->service );
		$this->config->attach( $this->indexer );
		$this->config->attach( $this->api );
	}
}