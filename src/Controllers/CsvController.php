<?php
namespace UCDavis\Controllers;

use UCDavis\DataAccess\DatasetDAO;
use UCDavis\DataAccess\DatasetInfoDAO;
use UCDavis\DataAccess\ChartTypeDAO;
use UCDavis\Controllers\Services\CsvServices;
use UCDavis\Controllers\Services\FileTypes;

use UCDavis\Exceptions\InvalidDatasetJson;

/**
 * Handles uploading of excel to database. DO NOT USE
 * with public facing application. Not secure.
 */
class CsvController
{
	private $csv;
	private $headers;
	private $datasetJson;
	private $dataTypes;

	/**
	 * Inserts dataset into database. Gets file from
	 * stream, formats data then inserts.
	 */
	public function insertDataset()
	{
		$dao = new DatasetDAO('charts');
		$daoInfo = new DatasetInfoDAO('charts');
		$daoChartType = new ChartTypeDAO;

		$this->getCsv();
		$this->getColumnNames();
		$this->getDataTypesArray();
		// TODO: Create a sproc to handle database queries.
		$tableName = sha1($this->datasetJson->tableName);
		$dao->createDatasetTable($tableName, $this->headers, $this->dataTypes);
		$dao->insertDataset($tableName, $this->csv);
		$daoInfo->insertDatasetInfo($this->datasetJson);
		
		$chartTypes = $this->datasetJson->chartTypes;
		$datasetId = $daoInfo->getDatasetId($tableName)[0]['id'];
	  for ($i = 0; $i < count($chartTypes); $i++) {
			$daoChartType->insertChartTypeByDatasetId($chartTypes[$i], $datasetId);
		}

		echo "done";
	}

	/**
	 * Gets uploaded file from stream.
	 */
	private function getCsv()
	{
		$str_json = file_get_contents('php://input');

		if (!isset($str_json)) {
			throw new InvalidDatasetJson();			
		}

		$this->datasetJson = json_decode($str_json);

    $csvService = new CsvServices($this->datasetJson->base64);

    /**
     * Check from $this->datasetJson if file type is xls, xlsx, or csv
     * If none of the above, return
     */
    if ($this->datasetJson->ext == FileTypes::CSV) {
        $this->csv = $csvService->convertCsvToArray();
    }
		else if ($this->datasetJson->ext == FileTypes::XLS || $this->datasetJson->ext == FileTypes::XLSX) {
        $this->csv = $csvService->convertExcelToArray();
    }
	}

	/**
	 * Strips header from uploaded Excel file.
	 */
	private function getColumnNames()
	{
		$this->headers = array_shift($this->csv);	
	}

	/**
	 * Creates an array of mySQL data types based on the data types of the
	 * data in the first row of the spreadsheet
	 */
	private function getDataTypesArray() {
		$firstDataRow = $this->csv[0]; //get first data row for detecting data types
		$types = array();
		foreach($firstDataRow as $data) {
			if ((string)(int)$data == $data) {
				array_push($types, "int(20)");
			}
			else if ((string)(float)$data == $data) {
				array_push($types, "decimal(20, 6)");
			}
			else {
				array_push($types, "varchar(250)");
			}
		}

		$this->dataTypes = $types;
	}
}
?>
