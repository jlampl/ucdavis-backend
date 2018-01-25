<?php
namespace UCDavis\DataAccess;

use UCDavis\Connections\SQLConnection;

class DatasetDAO
{
	const BASE_QUERY = 'select * from ';

	private $connection;

	public $isConnected;

	public function __construct($dbName)
	{
		$this->connection = new SQLConnection($dbName);

		if ($this->connection->isConnected) {
			$this->isConnected = true;
		} else {
			$this->isConnected = false;
		}
	}

	public function getDataset($datasetId)
	{
		$tableName = $this->getDatasetName($datasetId);

		$sql = self::BASE_QUERY . $tableName;

		$result = $this->connection->runQuery($sql);

		return $result;
	}

	public function getDatasetName($datasetId)
	{
		$sql = 'select TableName from datasetinfo where ID=' . $datasetId;

		$result = $this->connection->runQuery($sql);
		
		return $result[0]['TableName'];
	}

	public function deleteDataset($datasetId)
	{
		$sql = 'update datasetinfo set delete_dataset=1 where ID=' . $datasetId;
		
		$result = $this->connection->runQuery($sql);
		
		//return $result;
		echo $sql;
	}

	public function reenableDataset($datasetId)
	{
		$sql = 'update datasetinfo set delete_dataset=0 where ID=' . $datasetId;

		$result = $this->connection->runQuery($sql);

		echo $sql;
	} 
	
	public function createDatasetTable($tableName, $headers, $dataTypes)	
	{
		$sql = "create table $tableName (" . $this->createDatasetColumns($headers, $dataTypes)
			. ");";
		$this->connection->runQuery($sql);
		// echo $sql;
	}

	public function insertDataset($tableName, $data)
	{
		$sql = "insert into $tableName values". $this->createDataString($data) . ";";
		
		$this->connection->runQuery($sql);
		// echo $sql;
	}

	private function createDatasetColumns($headers, $dataTypes)	
	{
		$columnName = "";

		for ($i = 0; $i < count($headers); $i++) {
			$columnName = $columnName . str_replace(" ", "_","`$headers[$i]`") . ' ' . $dataTypes[$i] . ', ';				
		}

		if (strlen($columnName) > 1) {
			$columnName = chop($columnName, ", ");
		}

		return $columnName;
	}

	private function createDataString($data)
	{
		$insStatement = "";
		
		for ($i = 0; $i < count($data); $i++) {
			$values = "(";

			foreach($data[$i] as $entry) {
				$values = $values . " " . $this->getEntry($entry) . ",";
			}
			
			if (strlen($values) > 1) {
				$values = chop($values, ",") . "),";
			}

			$insStatement = $insStatement . $values;
		}

		if (strlen($values) > 1) {
			$insStatement = chop($insStatement, ",");
		}

		return $insStatement;
	}

	private function getEntry($entry) {
		if ((string)(int)$entry == $entry || (string)(float)$entry == $entry) {
			return $entry;
		}
		else {
			return "'$entry'";
		}
	}

	public function getDatasetChartTypes($chartType) {
		if ($this->isConnected) {
			$sql = "select datasetinfo.ID as ID, datasetinfo.title, datasetinfo.description, 
			datasetinfo.TableName as TableName, datasetinfo.delete_dataset, datasetinfo.featured from datasetinfo, charttype, datasetcharttype 
			where datasetinfo.ID = datasetcharttype.ID and datasetcharttype.ChartID = charttype.ChartID and delete_dataset=0
			and charttype.ChartName='" . $chartType . "' order by featured desc";

			$result = $this->connection->runQuery($sql);

			return $result;
		}
	}
}
?>
