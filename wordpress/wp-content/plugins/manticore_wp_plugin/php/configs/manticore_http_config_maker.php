<?php

class Manticore_Http_Config_Maker extends Manticore_Config_Maker {


	protected function setIndexPath() {
		$this->index_path = '/var/lib/manticore';
	}

	protected function get_searchd_section() {

		return "searchd\r\n" .
		       "{\r\n" .
		       "\t listen                          = 9306:mysql41\r\n" .
		       "\t listen                          = 9308:http\r\n" .
		       "\t binlog_path                     = # disable logging\r\n" .
		       "\t read_timeout                    = 5\r\n" .
		       "\t max_children                    = 30\r\n" .
		       "\t max_packet_size                 = 32M\r\n" .
		       "\t pid_file                        = /var/run/searchd.pid\r\n" .
		       "\t log                             = /var/lib/manticore/log/searchd.log\r\n" .
		       "\t query_log                       = /var/lib/manticore/log/query.log\r\n" .
		       "\t query_log_format                = sphinxql\r\n" .
		       "\t mysql_version_string            = 5.5.21\r\n" .
		       "\t workers                         = thread_pool\r\n" .
		       "}\r\n";
	}
}

?>