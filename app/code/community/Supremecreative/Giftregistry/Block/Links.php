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
 * Links block
 *
 * @category    Supremecreative
 * @package     Supremecreative_Giftregistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Supremecreative_Giftregistry_Block_Links extends Mage_Page_Block_Template_Links_Block
{
    /**
     * Position in link list
     * @var int
     */
    protected $_position = 30;

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helper('giftregistry')->isAllow()) {
            $text = $this->_createLabel($this->_getItemCount());
            $this->_label = $text;
            $this->_title = $text;
            $this->_url = $this->getUrl('giftregistry');
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Count items in giftregistry
     *
     * @return int
     */
    protected function _getItemCount()
    {

        $giftregistryId = $this->helper('giftregistry')->getGiftregistry();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        
        if($customerId != $giftregistryId) {
            
            $giftregistry = Mage::getModel('giftregistry/giftregistry')->load($customerId, 'customer_id');
            if($customerId != $giftregistry->getId) {
                return;
            }

        }        
        
        
        return $this->helper('giftregistry')->getItemCount();
    }

    /**
     * Create button label based on giftregistry item quantity
     *
     * @param int $count
     * @return string
     */
    protected function _createLabel($count)
    {
        if ($count > 1) {
            return $this->__('My Gift Registry (%d items)', $count);
        } else if ($count == 1) {
            return $this->__('My Gift Registry (%d item)', $count);
        } else {
            return $this->__('My Gift Registry');
        }
    }

    /**
     * Retrieve block cache tags
     *
     * @return array
     */
    public function getCacheTags()
    {
        /** @var $giftregistry Supremecreative_Giftregistry_Model_Giftregistry */
        $giftregistry = $this->helper('giftregistry')->getGiftregistry();
        $this->addModelTags($giftregistry);
        foreach ($giftregistry->getItemCollection() as $item) {
            $this->addModelTags($item);
        }
        return parent::getCacheTags();
    }
}
