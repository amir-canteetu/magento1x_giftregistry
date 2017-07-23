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

class Supremecreative_Giftregistry_Block_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
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
    public function getCustomerSharedGiftregistry() 
    {
        return $this->_getHelper()->getCustomerSharedGiftregistry();
    }    
    
}
