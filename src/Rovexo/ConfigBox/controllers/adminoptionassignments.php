<?php
defined('CB_VALID_ENTRY') or die();

class ConfigboxControllerAdminoptionassignments extends KenedoController {

	/**
	 * @return ConfigboxModelAdminoptionassignments
	 */
	protected function getDefaultModel() {
		return KenedoModel::getModel('ConfigboxModelAdminoptionassignments');
	}

	/**
	 * @return ConfigboxViewAdminoptionassignments
	 */
	protected function getDefaultView() {
		return $this->getDefaultViewList();
	}

	/**
	 * @return ConfigboxViewAdminoptionassignments
	 */
	protected function getDefaultViewList() {
		return KenedoView::getView('ConfigboxViewAdminoptionassignments');
	}

	/**
	 * @return ConfigboxViewAdminoptionassignment
	 */
	protected function getDefaultViewForm() {
		return KenedoView::getView('ConfigboxViewAdminoptionassignment');
	}

    /**
     * This override makes the xref data sent back in the response (since we split the data in xref and option data)
     * @throws Exception
     */
    function store() {

        // Check authorization, abort if negative
        $this->isAuthorized() or $this->abortUnauthorized();

        // Get the default model
        $model = $this->getDefaultModel();

        if (!$model) {
            throw new Exception('Store task called, but there is no model assigned to that controller. Check method getDefaultModel.');
        }

        // Make a normalized data object from HTTP request data
        $data = $model->getDataFromRequest();

        // Prepare the data (auto-fill data like empty URL segment fields and similar)
        $model->prepareForStorage($data);

        // Check if the data validates
        $checkResult = $model->validateData($data);

        $isInsert = $model->isInsert($data);

        // Abort and send feedback if validation fails
        if ($checkResult === false) {
            KenedoPlatform::p()->setDocumentMimeType('application/json');
            $response = new stdClass();
            $response->success = false;
            $response->errors = $model->getErrors();
            echo json_encode($response);
            return;
        }

        // Get the data stored
        $success = $model->store($data);

        // Run the afterSave stuff
        $this->afterStore($success);

        // Abort and send feedback if storage fails
        if ($success === false) {
            KenedoPlatform::p()->setDocumentMimeType('application/json');
            $response = new stdClass();
            $response->success = false;
            $response->errors = $model->getErrors();
            echo json_encode($response);
            return;
        }

        // Purge the cache
        $this->purgeCache();

        // Bring the good news
        KenedoPlatform::p()->setDocumentMimeType('application/json');
        $response = new stdClass();
        $response->success = true;
        $response->messages = array();
        $response->wasInsert = $isInsert;
        $response->messages[] = KText::_('Record saved.');

        // Add the current record data to the response
        if (!empty($data['xref']->id)) {
            $response->data = $model->getRecord($data['xref']->id);
        }
        else {
            $response->data = NULL;
        }

        if (KRequest::getKeyword('task') == 'apply') {
            // On inserts, we redirect to the right edit URL (have the right ID set)
            if ($isInsert) {
                // Get the controller name
                $controllerName = KenedoController::getControllerNameFromClass(get_class($this));
                // Get the redirect URL
                $url = 'index.php?option='.$this->component.'&controller='.$controllerName.'&task=edit&id='.$data['xref']->id;
                // If the return param is sent along, append it
                if (KRequest::getString('return')) {
                    $url .= '&return='.KRequest::getString('return');
                }
                // Get it all together
                $response->redirectUrl = KLink::getRoute($url, false);
            }
        }
        else {
            if (KRequest::getString('return')) {
                $url = KLink::base64UrlDecode(KRequest::getString('return'));
            }
            else {
                // Get the controller name
                $controllerName = KenedoController::getControllerNameFromClass(get_class($this));
                // Get the redirect URL
                $url = KLink::getRoute('index.php?option='.$this->component.'&controller='.$controllerName, false);
            }

            $response->redirectUrl = $url;
        }

        echo json_encode($response);

    }

    function copyWithGlobalOption() {

	}

}
