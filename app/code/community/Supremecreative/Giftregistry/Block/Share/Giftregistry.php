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
 * @category    Mage
 * @package     Supremecreative_Giftregistry
 * @copyright  Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Giftregistry block shared items
 *
 * @category   Mage
 * @package    Supremecreative_Giftregistry
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Supremecreative_Giftregistry_Block_Share_Giftregistry extends Supremecreative_Giftregistry_Block_Abstract
{
    /**
     * Customer instance
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer = null;

    /**
     * Prepare global layout
     *
     * @return Supremecreative_Giftregistry_Block_Share_Giftregistry
     *
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->getHeader());
        }
        return $this;
    }

    /**
     * Retrieve Shared Gift Registry Customer instance
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getGiftregistryCustomer()
    {
        if (is_null($this->_customer)) {
            $this->_customer = Mage::getModel('customer/customer')
                ->load($this->_getGiftregistry()->getCustomerId());
        }

        return $this->_customer;
    }
    
    /**
     * Retrieve Shared Gift Registry Type instance
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getGiftregistryType()
    {
        return Mage::helper('giftregistry')->getGiftregistryType();
    }    

    /**
     * Retrieve Page Header
     *
     * @return string
     */
    public function getHeader()
    {
        return Mage::helper('giftregistry')->__("%s's %s Gift Registry", $this->escapeHtml($this->getGiftregistryCustomer()->getFirstname()), $this->escapeHtml($this->getGiftregistryType()->getName()));
    }
}