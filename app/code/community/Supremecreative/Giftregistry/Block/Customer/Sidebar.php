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
 * Giftregistry sidebar block
 *
 * @category   Mage
 * @package    Supremecreative_Giftregistry
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Supremecreative_Giftregistry_Block_Customer_Sidebar extends Supremecreative_Giftregistry_Block_Abstract
{
    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->__('My Gift Registry <small>(%d)</small>', $this->getItemCount());
    }

    /**
     * Add sidebar conditions to collection
     *
     * @param  Supremecreative_Giftregistry_Model_Resource_Item_Collection $collection
     * @return Supremecreative_Giftregistry_Block_Customer_Giftregistry
     */
    protected function _prepareCollection($collection)
    {
        $collection->setCurPage(1)
            ->setPageSize(3)
            ->setInStockFilter(true)
            ->setOrder('added_at');

        return $this;
    }

    /**
     * Prepare before to html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getItemCount()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Can Display giftregistry
     *
     * @deprecated after 1.6.2.0
     * @return bool
     */
    public function getCanDisplayGiftregistry()
    {
        return $this->_getCustomerSession()->isLoggedIn();
    }

    /**
     * Retrieve URL for removing item from giftregistry
     *
     * @deprecated back compatibility alias for getItemRemoveUrl
     * @param  Supremecreative_Giftregistry_Model_Item $item
     * @return string
     */
    public function getRemoveItemUrl($item)
    {
        return $this->getItemRemoveUrl($item);
    }

    /**
     * Retrieve URL for adding product to shopping cart and remove item from giftregistry
     *
     * @deprecated
     * @param  Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $product
     * @return string
     */
    public function getAddToCartItemUrl($product)
    {
        return $this->getItemAddToCartUrl($product);
    }

    /**
     * Retrieve Giftregistry Product Items collection
     *
     * @return Supremecreative_Giftregistry_Model_Resource_Item_Collection
     */
    public function getGiftregistryItems()
    {
        if (is_null($this->_collection)) {
            $this->_collection = clone $this->_createGiftregistryItemCollection();
            $this->_collection->clear();
            $this->_prepareCollection($this->_collection);
        }

        return $this->_collection;
    }

    /**
     * Return giftregistry items count
     *
     * @return int
     */
    public function getItemCount()
    {
        return $this->_getHelper()->getItemCount();
    }

    /**
     * Check whether user has items in his giftregistry
     *
     * @return bool
     */
    public function hasGiftregistryItems()
    {
        return $this->getItemCount() > 0;
    }

    /**
     * Retrieve cache tags
     *
     * @return array
     */
    public function getCacheTags()
    {
        if ($this->getItemCount()) {
            $this->addModelTags($this->_getHelper()->getGiftregistry());
        }
        return parent::getCacheTags();
    }


}
