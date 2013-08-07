<?php

App::uses('AppController', 'Controller');

class PostmarkAppController extends AppController {
  
/**
 * inbound method
 * Web entry point. Takes posted data and diverts it to 
 * the set model.
 *
 * @return CakeResponse empty response
 */
  public function inbound() {
    if($this->request->is('post')) {
      $json = $this->request->input('json_decode', true);

      if (empty($json) || json_last_error() !== JSON_ERROR_NONE) {
        throw new BadRequestException('JSON format error');
      }

      $this->_createFromInboundHook($json);

      return new CakeResponse(); // Empty 200 response
    }
  }

/**
 * _createFromInboundHook method
 * Takes data from the inbound method and formats and sends
 * to the model.
 *
 * @return boolean Result of the model's callback, or true
 */
  protected function _createFromInboundHook($data) {
    // Apply some standard formatting
    $data = $this->_formatData($data);

    $Model = $this->_getModel();
    if(method_exists($Model, 'createFromPostmarkHook')) {
      return $Model->createFromPostmarkHook($data);
    }
    return true;
  }

/**
 * _formatData method
 * Takes Postmark JSON data and formats the headers
 *
 * @return array Modified $data
 */
  protected function _formatData($data) {
    $data['Headers'] = Hash::combine($data, 'Headers.{n}.Name', 'Headers.{n}.Value');
    return $data;
  }

/**
 * _getModel method
 * Returns the model specified in the Configuration.
 * This model should have the callback method.
 *
 * @return mixed Model or false
 */
  protected function _getModel() {
    $model = Configure::read('Postmark.Inbound.model');
    return $model ? ClassRegistry::init($model) : false;
  }
  
}