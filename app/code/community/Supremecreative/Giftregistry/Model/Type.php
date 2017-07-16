<?php

/**
 * Supremecreative (Pty) Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to amir@supremecreative.co.za so I can send you a copy.
 *
 * @copyright   Copyright (c) 2017 Supremecreative (Pty) Ltd
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Supremecreative_Giftregistry_Model_Type extends Mage_Core_Model_Abstract {
    
    /**
     * Initialize type model (Standard model initialization). Sets resource names: protected function _setResourceModel($resourceName, $resourceCollectionName=null)
     */
    function _construct()  {
        $this->_init('giftregistry/type');
    }
    
    public function setRegistryTypeInfo($data) 
    {
        
        try {
            if ($data) {

                $this->setName($data['custom_event_type']);
                $this->setCustomerId(Mage::getSingleton('customer/session')->getCustomer()->getId());                
                $this->setStoreId(Mage::app()->getStore()->getStoreId());
                $this->setIsActive(1);

            } else {
                throw new Exception("Error Processing Request: Insufficient Data Provided");
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;        
        
    }
    
    protected function validateNotEmpty() 
    {
        
        $errors = array();
        $nonemptyValidator = new Zend_Validate_NotEmpty(); 
            
        if (!$nonemptyValidator->isValid($this->getName())) {
          $errors[] = Mage::helper('giftregistry')->__('Please provide an The Event Type Name.');  
        } 
        
        if (!$nonemptyValidator->isValid($this->getStoreId())) {
          $errors[] = Mage::helper('giftregistry')->__('There was an error in creating the registry type; no store ID was provided.');  
        } 

        if (!$nonemptyValidator->isValid($this->getIsActive())) {
          $errors[] = Mage::helper('giftregistry')->__('There was an error in creating the registry type; the registry should be enabled.');  
        } 
              
        return $errors;
                
    }

    public function validate() 
    {
        
        $errors = $this->validateNotEmpty();
        $digitValidator = new Zend_Validate_Digits();
            
        if(!$digitValidator->isValid($this->getStoreId())) {
            $errors[] = Mage::helper('giftregistry')->__('The store ID should have only digit characters');
        }
            
        if(!$digitValidator->isValid($this->getIsActive())) {
            $errors[] = Mage::helper('giftregistry')->__('The is_active data should have only digit characters');
        }        
        
        return $errors;
    }     
    
}













