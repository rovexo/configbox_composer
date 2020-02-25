<?php
defined('CB_VALID_ENTRY') or die();

class KenedoDatabaseMagento2 extends KenedoDatabase {

	/**
	 * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
	 * @return bool
	 */
	function changeConnection($connection) {

		if (is_a($this->link , 'mysqli') || is_resource($this->link)) {
			mysqli_close($this->link);
		}

		$config = $connection->getConfig();

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		/**
		 * @var \Magento\Framework\App\DeploymentConfig $deploymentConfig
		 */
		$deploymentConfig = $objectManager->get('Magento\Framework\App\DeploymentConfig');
		$prefix = $deploymentConfig->get('db/table_prefix');

		$this->hostname = $config['host'];
		$this->username = $config['username'];
		$this->password = $config['password'];
		$this->database = $config['dbname'];
		$this->prefix = $prefix;

		if (strpos($this->hostname,':') !== false) {
			$ex = explode(':', $this->hostname);
			$this->hostname = $ex[0];
			if (is_numeric($ex[1])) {
				$this->port = $ex[1];
			}
			else {
				$this->socket = $ex[1];
			}
		}

		$this->link = mysqli_connect( $this->hostname, $this->username, $this->password, $this->database, $this->port, $this->socket );

		if ($this->link == false) {
			$internalLogMessage = 'Could not establish a connection to the database. Connection error message is "'.mysqli_connect_error().'".';
			KLog::log($internalLogMessage,'db_error');
			KLog::log($internalLogMessage,'error', 'Could not establish a connection to the database. Check the configbox error log file for more information.');
			return false;
		}

		return true;

	}

}