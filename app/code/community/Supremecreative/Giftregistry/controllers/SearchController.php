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

class Supremecreative_Giftregistry_SearchController extends Mage_Core_Controller_Front_Action
{

    /**
     * Extend preDispatch
     *
     * @return Mage_Core_Controller_Front_Action|void
     */
    public function preDispatch()
    {
        parent::preDispatch();
        
        if (!Mage::getStoreConfigFlag('giftregistry/general/active')) {
            $this->norouteAction();
            return;
        }               

    }

    /**
     * Retrieve giftregistry object
     * @param int $giftregistryId
     * @return Supremecreative_Giftregistry_Model_Giftregistry|bool
     */
    protected function _getGiftRegistry($giftregistryId = null) 
    {
        
        if ($giftregistry = Mage::registry('giftregistry')) {
            return $giftregistry;
        }

        try {
            
            $giftregistry = Mage::getModel('giftregistry/giftregistry');
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            if (!$giftregistryId) {
                $giftregistryId = $this->getRequest()->getParam('registry_id');
            }
            
            if ($giftregistryId) {
                $giftregistry->load($giftregistryId);
            } else {
                $giftregistry->load($customerId, 'customer_id');
            }
            
            if (!$giftregistry->getGiftregistryID() || $giftregistry->getCustomerId() != $customerId) {
                $giftregistry = null;
            }            

            Mage::register('giftregistry', $giftregistry);

        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('giftregistry/session')->addError($e->getMessage());
            return false;
        } catch (Exception $e) {
            Mage::getSingleton('giftregistry/session')->addException($e, Mage::helper('giftregistry')->__('The gift registry could not be retrieved.') );
            return false;
        }

        return $giftregistry;        
    }

    /**
     * Display customer gift registry search page
     */
    public function indexAction()
    {

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('giftregistry/session');        
        
        $this->renderLayout();
        return $this;        

    }
    
    

    /**
     * Display customer gift registry search page
     */
    public function resultsAction()
    {

        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }
        
        $this->loadLayout();
        
        if($searchData = $this->getRequest()->getPost()) {
            
            $giftregistryCollection = Mage::getModel('giftregistry/giftregistry')->getCollection();
            
            if($searchData['type_id']) {
                $giftregistryCollection->addFieldToFilter('type_id', $searchData['type_id']);
            }
            
            if($searchData['sharing_code']) {
                $giftregistryCollection->addFieldToFilter('sharing_code', $searchData['sharing_code']);
            }

            $this->getLayout()->getBlock('giftregistry.search.results')->setSearchResults($giftregistryCollection);            
            
        }

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('giftregistry/session');        
        
        $this->renderLayout();
        return $this;        

    }    
    
    
    
    
    
}
