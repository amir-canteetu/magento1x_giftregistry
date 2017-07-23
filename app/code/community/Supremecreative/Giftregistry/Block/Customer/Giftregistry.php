<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Supremecreative
 * @package     Supremecreative_Giftregistry
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Giftregistry block customer items
 *
 * @category   Mage
 * @package    Supremecreative_Giftregistry
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Supremecreative_Giftregistry_Block_Customer_Giftregistry extends Supremecreative_Giftregistry_Block_Abstract
{
    /*
     * List of product options rendering configurations by product type
     */
    protected $_optionsCfg = array();

    /**
     * Add giftregistry conditions to collection
     *
     * @param  Supremecreative_Giftregistry_Model_Mysql4_Item_Collection $collection
     * @return Supremecreative_Giftregistry_Block_Customer_Giftregistry
     */
    protected function _prepareCollection($collection)
    {
        $collection->setInStockFilter(true)->setOrder('added_at', 'ASC');
        return $this;
    }

    /**
     * Preparing global layout
     *
     * @return Supremecreative_Giftregistry_Block_Customer_Giftregistry
     * 
     * 
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__('My Gift Registry'));
        }
    }
     */


   
    /**
     * Check if logged in user has shipping addresses saved
     * @return Mage_Checkout_Block_Onepage_Shipping
     */
    public function customerHasAddresses()
    {
        return Mage::getBlockSingleton('checkout/onepage_shipping')->customerHasAddresses();
    }    
    
    /**
     * Get logged in user addresses
     * @return Mage_Checkout_Block_Onepage_Shipping
     */
    public function getAddressesHtmlSelect($type)
    {
        return Mage::getBlockSingleton('giftregistry/onepage_shipping')->getAddressesHtmlSelect($type);
    } 

    /**
     * Get logged in user addresses
     * @return Mage_Customer_Model_Address
     */
    public function getGiftRegistryAddress()
    {
        $giftRegistryInstance = $this->getGiftregistryInstance();
        
        if($giftRegistryInstance) {
            $addressId = $giftRegistryInstance->getShippingAddressId();
            if($addressId) {
                $address = Mage::getModel('customer/address')->load($addressId);
                return Mage::getModel('customer/address')->load($addressId);
            }
        }
        
        return '';
        
    }     
    
    
    
}
