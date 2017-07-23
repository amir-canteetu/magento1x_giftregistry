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

class Supremecreative_Giftregistry_Block_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping 
{   
    
    /**
     * Retrieve Giftregistry Data Helper
     *
     * @return Supremecreative_Giftregistry_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('giftregistry');
    }    
    
    /**
     * Retrieve Customer shared gift registry
     *
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */    
    public function getCustomerSharedGiftregistry() {

        return $this->_getHelper()->getCustomerSharedGiftregistry();
    }
    
    /**
     * Get owner/creator of gift registry
     *
     * @return Mage_Customer_Model_Customer
     */      
    public function getSharedGiftRegistryOwner() {

        if($sharedGiftRegistry = $this->getCustomerSharedGiftregistry()) {
            $sharedGiftRegistryOwnerId = $sharedGiftRegistry->getCustomerId();
            return Mage::getModel('customer/customer')->load($sharedGiftRegistryOwnerId);
        }
        
    }
    
    
    /**
     * Check if owner/creator of gift registry has any addresses
     *
     * @return int
     */     
    public function registryOwnerHasAddresses() 
    {
        
        return count($this->getSharedGiftRegistryOwner()->getAddresses());
        
    }
    
    /**
     * Get gift registry owner and logged-in customer shipping addresses
     *
     * @return Mage_Core_Block_Html_Select
     */      
     public function getAddressesHtmlSelect($type)
    {
        
        if(!$this->getCustomerSharedGiftregistry()) {
            return parent::getAddressesHtmlSelect($type);
        } 
         
        $options = array();

        foreach ($this->getSharedGiftRegistryOwner()->getAddresses() as $address) {
            $options[] = array(
                'value' => $address->getId(),
                'label' => $address->format('oneline')
            );
        } 

        if ($this->isCustomerLoggedIn()) {
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }
        }

        $address = $this->getSharedGiftRegistryOwner()->getPrimaryShippingAddress();
        $addressId = $address->getId();

        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'_address_id')
            ->setId($type.'-address-select')
            ->setClass('address-select')
            ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
            ->setValue($addressId)
            ->setOptions($options);

        $select->addOption('', Mage::helper('checkout')->__('New Address'));

        return $select->getHtml();
        
    }   
    
    
    
    
     public function getShippingAddressesHtmlSelect($type)
    {

        $options = array();
        foreach ($this->getCustomer()->getAddresses() as $address) {
            $options[] = array(
                'value' => $address->getId(),
                'label' => $address->format('oneline')
            );
        }

        $addressId = $this->getAddress()->getCustomerAddressId();
        if (empty($addressId)) {
            $address = $this->getCustomer()->getPrimaryShippingAddress();
            if ($address) {
                $addressId = $address->getId();
            }
        }

        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'_address_id')
            ->setId($type.'-address-select')
            ->setClass('address-select')
            ->setValue($addressId)
            ->setOptions($options);

        return $select->getHtml();
        
    }     
    
}

