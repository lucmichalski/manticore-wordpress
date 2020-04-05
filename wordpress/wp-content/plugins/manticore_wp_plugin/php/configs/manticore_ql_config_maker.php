<?php

class Manticore_Ql_Config_Maker extends Manticore_Config_Maker {

	protected function setIndexPath() {
		$this->index_path = $this->config->admin_options['sphinx_path']."/var";
	}

	protected function get_searchd_section() {
		$connection_type = "listen \t\t\t = ";
		if ( $this->config->admin_options['sphinx_use_socket'] == 'true' ) {
			$connection_type .= $this->config->admin_options['sphinx_socket'] . ":mysql41";

		} elseif ( $this->config->admin_options['sphinx_use_socket'] == 'false' ) {
			$connection_type .= $this->config->admin_options['sphinx_host'] . ":" . $this->config->admin_options['sphinx_port'] . ":mysql41";
		}

		return "searchd\r\n" .
		       "{\r\n" .
		       "\t " . $connection_type . "\r\n" .
		       "\t binlog_path                     = # disable logging\r\n" .
		       "\t read_timeout                    = 5\r\n" .
		       "\t max_children                    = 30\r\n" .
		       "\t max_packet_size                 = 32M\r\n" .
		       "\t pid_file                        = " . $this->index_path . "/log/searchd.pid\r\n" .
		       "\t log                             = " . $this->index_path . "/log/searchd.log\r\n" .
		       "\t query_log                       = " . $this->index_path . "/log/query.log\r\n" .
		       "\t query_log_format                = sphinxql\r\n" .
		       "}\r\n";
	}
}

?>