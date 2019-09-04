<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdmincalcmatrices extends KenedoController {

	/**
	 * @return ConfigboxModelAdmincalcmatrices
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdmincalcmatrices');
	}

	/**
	 * @return ConfigboxViewAdmincalcmatrix
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewForm();
	}

	/**
	 * @return NULL
	 */
	protected function getDefaultViewList() {
		return NULL;
	}

	/**
	 * @return ConfigboxViewAdmincalcmatrix
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdmincalcmatrix');
	}

	/**
	 * Takes in an .xls or .xlsx file from request data and returns matrix values (two dimensional array with row, then
	 * column numbers as keys (starting with 0), cell values as values
	 * @throws Exception if anything is wrong with the Excel file
	 */
	public function getMatrixDataFromSpreadsheet() {
		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();
		$msg = '';

		// get file
		$file = KRequest::getFile('file');
		if(!$file){
			$msg .= 'No File has been uploaded.';
			echo json_encode(['success'=>false, 'message' => $msg]);
			return;
		}

		$inputFileName = $file['tmp_name'];
		$userFileName = $file['name'];

		// get extension
		$tmpExtension = substr($inputFileName, strrpos($inputFileName, '.'));
		$userExtension = substr($userFileName, strrpos($userFileName, '.'));
		$newFileName = str_replace($tmpExtension, $userExtension, $inputFileName);
		$copyResult = rename($inputFileName, $newFileName);
		if($copyResult) $inputFileName = $newFileName;

		//  Include PHPExcel_IOFactory
		require_once KenedoPlatform::p()->getComponentDir('com_configbox').DS.'external'.DS.'phpexcel/PHPExcel/IOFactory.php';

		//  Read your Excel workbook
		try {
			// extension check
			if(!in_array(strtolower($userExtension),['.xls', '.xlsx'])){
				$msg .= $extErrMsg = 'Not an Excel file.';
				throw new Exception($extErrMsg);
			}
			$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
			if(empty($inputFileType)) {
				$msg .= $extErrMsg = 'Could not identify an Excel file.';
				throw new Exception($extErrMsg);
			}
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			if(empty($objReader)) {
				$msg .= $extErrMsg = 'Could not create Excel Reader.';
				throw new Exception($extErrMsg);
			}

			// Set read only (may bring a slight performance improvement)
			$objReader->setReadDataOnly(true);

			$objPHPExcel = $objReader->load($inputFileName);
			if(empty($objPHPExcel)) {
				$msg .= $extErrMsg = 'Could not load Excel File Object.';
				throw new Exception($extErrMsg);
			}
			//  Get worksheet dimensions
			$sheet = $objPHPExcel->getSheet(0);
			if(empty($sheet)) {
				$msg .= $extErrMsg = 'Could not get Excel Sheet.';
				throw new Exception($extErrMsg);
			}
			$highestRow = $sheet->getHighestRow();
			if(empty($highestRow)) {
				$msg .= $extErrMsg = 'Could not get Excel highest Row.';
				throw new Exception($extErrMsg);
			}
			$highestColumn = $sheet->getHighestDataColumn();
			if(empty($highestColumn)) {
				$msg .= $extErrMsg = 'Could not get Excel highest Column.';
				throw new Exception($extErrMsg);
			}

		} catch(Exception $e) {
			KLog::log('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": ','error');//.$e->getMessage());
			$msg .= ' Error loading file `'.$file['name'].'`';
			echo json_encode(array('success' => false, 'message' => $msg));
			return;
		}

		// load Excel data
		$outputData = [];
		//  Loop through each row of the worksheet in turn
		for ($row = 1; $row <= $highestRow; $row++) {
			//  Read a row of data into an array
			$excelData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
				NULL,
				TRUE,
				FALSE);
			if(!empty($excelData[0])) $outputData[] = $excelData[0];
		}

		if(empty($outputData)) {
			$msg .= 'Could not get Excel output data.';
			echo json_encode(['success'=>false, 'message' => $msg]);
			return;
		}

		echo json_encode(['success'=>true, 'data' => $outputData]);

		// delete input file
		if(file_exists($inputFileName)) unlink($inputFileName);

	}

	function edit() {

		// Check authorization, abort if negative
		$this->isAuthorized() or $this->abortUnauthorized();

		$productId = KRequest::getInt('productId');
		$matrixId = KRequest::getInt('id');

		$view = $this->getDefaultViewForm();
		$view->setProductId($productId);
		$view->setMatrixId($matrixId);
		$view->display();

	}

}
