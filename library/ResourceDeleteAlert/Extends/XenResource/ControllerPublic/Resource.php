<?php

class ResourceDeleteAlert_Extends_XenResource_ControllerPublic_Resource extends XFCP_ResourceDeleteAlert_Extends_XenResource_ControllerPublic_Resource {

	public function actionDelete() {
		list($resource, $category) = $this->_getResourceHelper()->assertResourceValidAndViewable();

		$hardDelete = $this->_input->filterSingle('hard_delete', XenForo_Input::UINT);
		$deleteType = ($hardDelete ? 'hard' : 'soft');

		if (!$this->_getResourceModel()->canDeleteResource($resource, $category, $deleteType, $errorPhraseKey))
		{
			throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
		}

		if ($this->isConfirmedPost()) {
			$dw = XenForo_DataWriter::create('XenResource_DataWriter_Resource');
			$dw->setExistingData($resource['resource_id']);

			$rda_alert = $this->_input->filter(array(
				'rda_alert_enabled' => XenForo_Input::BOOLEAN,
				'rda_alert_reason' => XenForo_Input::STRING
			));
			if ($resource['resource_state'] == 'visible' && $rda_alert['rda_alert_enabled'] && $resource['user_id'] && $resource['user_id'] != XenForo_Visitor::getUserId()) {
				//$users = $this->_getResourceWatchModel()->getUsersWatchingResource($dw->get("resource_id"), $dw->get("resource_category_id"));
				$users = $this->_getDownloadUsers($resource['resource_id']);
            	foreach($users as $user) {
					$reason = "The resource " . $resource['title'] . " has been deleted. Reason: " . $rda_alert['rda_alert_reason'];
	                $extra_data = [
	                    "from_user" => 0,
	                    "link_url" => "",
	                    "link_title" => "",
	                    "alert_body" => $reason,
	                    "user_id" => 0,
	                    "alert_text" => $reason
	                ];

	                $this->_getAlertModel()->alertUser(
	                    $user['user_id'], 
	                    0, 
	                    "", 
	                    "user", 
	                    $resource['resource_id'], 
	                    "from_admin",
	                    $extra_data
	                );
				}
			}
		}
		return parent::actionDelete();
	}

	protected function _getDownloadUsers($resource_id) {
		$db = XenForo_Application::getDb();
		return $db->fetchAll("SELECT user_id FROM xf_resource_download WHERE resource_id = ?", $resource_id);
	}

		/**
	 * @return XenForo_Model_Alert
	 */
	protected function _getAlertModel()
	{
		return $this->getModelFromCache('XenForo_Model_Alert');
	}

		/**
	 * @return XenResource_ControllerHelper_Resource
	 */
	protected function _getResourceHelper()
	{
		return $this->getHelper('XenResource_ControllerHelper_Resource');
	}

	/**
	 * @return XenResource_Model_Resource
	 */
	protected function _getResourceModel()
	{
		return $this->getModelFromCache('XenResource_Model_Resource');
	}

	/**
	 * @return XenResource_Model_ResourceWatch
	 */
	protected function _getResourceWatchModel()
	{
		return $this->getModelFromCache('XenResource_Model_ResourceWatch');
	}
}