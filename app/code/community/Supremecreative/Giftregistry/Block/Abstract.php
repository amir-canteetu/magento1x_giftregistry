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
 * Giftregistry Product Items abstract Block
 *
 * @category   Mage
 * @package    Supremecreative_Giftregistry
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Supremecreative_Giftregistry_Block_Abstract extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Giftregistry Product Items Collection
     *
     * @var Supremecreative_Giftregistry_Model_Resource_Item_Collection
     */
    protected $_collection;

    /**
     * Store giftregistry Model
     *
     * @var Supremecreative_Giftregistry_Model_Giftregistry
     */
    protected $_giftregistry;

    /**
     * List of block settings to render prices for different product types
     *
     * @var array
     */
    protected $_itemPriceBlockTypes = array();

    /**
     * List of block instances to render prices for different product types
     *
     * @var array
     */
    protected $_cachedItemPriceBlocks = array();

    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addItemPriceBlockType('default', 'giftregistry/render_item_price', 'giftregistry/render/item/price.phtml');
    }

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
     * Retrieve Customer Session instance
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Retrieve Giftregistry model
     *
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    protected function _getGiftregistry()
    {
        return $this->_getHelper()->getGiftregistry();
    }

    /**
     * Prepare additional conditions to collection
     *
     * @param Supremecreative_Giftregistry_Model_Resource_Item_Collection $collection
     * @return Supremecreative_Giftregistry_Block_Customer_Giftregistry
     */
    protected function _prepareCollection($collection)
    {
        return $this;
    }

    /**
     * Create giftregistry item collection
     *
     * @return Supremecreative_Giftregistry_Model_Resource_Item_Collection
     */
    protected function _createGiftregistryItemCollection()
    {
        return $this->_getGiftregistry()->getItemCollection();
    }

    /**
     * Retrieve Giftregistry Product Items collection
     *
     * @return Supremecreative_Giftregistry_Model_Resource_Item_Collection
     */
    public function getGiftregistryItems()
    {
        if (is_null($this->_collection)) {
            $this->_collection = $this->_createGiftregistryItemCollection();
            $this->_prepareCollection($this->_collection);
        }

        return $this->_collection;
    }

    /**
     * Retrieve giftregistry instance
     *
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    public function getGiftregistryInstance()
    {
        return $this->_getGiftregistry();
    }

    /**
     * Back compatibility retrieve giftregistry product items
     *
     * @deprecated after 1.4.2.0
     * @return Supremecreative_Giftregistry_Model_Mysql4_Item_Collection
     */
    public function getGiftregistry()
    {
        return $this->getGiftregistryItems();
    }

    /**
     * Retrieve URL for Removing item from giftregistry
     *
     * @param Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     *
     * @return string
     */
    public function getItemRemoveUrl($item)
    {
        return $this->_getHelper()->getRemoveUrl($item);
    }

    /**
     * Retrieve Add Item to shopping cart URL
     *
     * @param string|Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     * @return string
     */
    public function getItemAddToCartUrl($item)
    {
        return $this->_getHelper()->getAddToCartUrl($item);
    }

    /**
     * Retrieve Add Item to shopping cart URL from shared giftregistry
     *
     * @param string|Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     * @return string
     */
    public function getSharedItemAddToCartUrl($item)
    {
        return $this->_getHelper()->getSharedAddToCartUrl($item);
    }

    /**
     * Retrieve URL for adding Product to giftregistry
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getAddToGiftregistryUrl($product)
    {
        return $this->_getHelper()->getAddUrl($product);
    }

     /**
     * Returns item configure url in giftregistry
     *
     * @param Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $product
     *
     * @return string
     */
    public function getItemConfigureUrl($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $id = $product->getGiftregistryItemId();
        } else {
            $id = $product->getId();
        }
        $params = array('id' => $id);

        return $this->getUrl('giftregistry/index/configure/', $params);
    }


    /**
     * Retrieve Escaped Description for Giftregistry Item
     *
     * @param Mage_Catalog_Model_Product $item
     * @return string
     */
    public function getEscapedDescription($item)
    {
        if ($item->getDescription()) {
            return $this->escapeHtml($item->getDescription());
        }
        return '&nbsp;';
    }

    /**
     * Check Giftregistry item has description
     *
     * @param Mage_Catalog_Model_Product $item
     * @return bool
     */
    public function hasDescription($item)
    {
        return trim($item->getDescription()) != '';
    }

    /**
     * Retrieve formated Date
     *
     * @param string $date
     * @return string
     */
    public function getFormatedDate($date)
    {
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
    }

    /**
     * Check is the giftregistry has a salable product(s)
     *
     * @return bool
     */
    public function isSaleable()
    {
        foreach ($this->getGiftregistryItems() as $item) {
            if ($item->getProduct()->isSaleable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve giftregistry loaded items count
     *
     * @return int
     */
    public function getGiftregistryItemsCount()
    {
        return $this->_getGiftregistry()->getItemsCount();
    }

    /**
     * Retrieve Qty from item
     *
     * @param Supremecreative_Giftregistry_Model_Item|Mage_Catalog_Model_Product $item
     * @return float
     */
    public function getQty($item)
    {
        $qty = $item->getQty() * 1;
        if (!$qty) {
            $qty = 1;
        }
        return $qty;
    }

    /**
     * Check is the giftregistry has items
     *
     * @return bool
     */
    public function hasGiftregistryItems()
    {
        return $this->getGiftregistryItemsCount() > 0;
    }

    /**
     * Adds special block to render price for item with specific product type
     *
     * @param string $type
     * @param string $block
     * @param string $template
     */
    public function addItemPriceBlockType($type, $block = '', $template = '')
    {
        if ($type) {
            $this->_itemPriceBlockTypes[$type] = array(
                'block' => $block,
                'template' => $template
            );
        }
    }

    /**
     * Returns block to render item with some product type
     *
     * @param string $productType
     * @return Mage_Core_Block_Template
     */
    protected function _getItemPriceBlock($productType)
    {
        if (!isset($this->_itemPriceBlockTypes[$productType])) {
            $productType = 'default';
        }

        if (!isset($this->_cachedItemPriceBlocks[$productType])) {
            $blockType = $this->_itemPriceBlockTypes[$productType]['block'];
            $template = $this->_itemPriceBlockTypes[$productType]['template'];
            $block = $this->getLayout()->createBlock($blockType)
                ->setTemplate($template);
            $this->_cachedItemPriceBlocks[$productType] = $block;
        }
        return $this->_cachedItemPriceBlocks[$productType];
    }

    /**
     * Returns product price block html
     * Overwrites parent price html return to be ready to show configured, partially configured and
     * non-configured products
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $displayMinimalPrice
     * @param string $idSuffix
     *
     * @return string
     */
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $type_id = $product->getTypeId();
        if (Mage::helper('catalog')->canApplyMsrp($product)) {
            $realPriceHtml = $this->_preparePriceRenderer($type_id)
                ->setProduct($product)
                ->setDisplayMinimalPrice($displayMinimalPrice)
                ->setIdSuffix($idSuffix)
                ->setIsEmulateMode(true)
                ->toHtml();
            $product->setAddToCartUrl($this->getAddToCartUrl($product));
            $product->setRealPriceHtml($realPriceHtml);
            $type_id = $this->_mapRenderer;
        }

        return $this->_preparePriceRenderer($type_id)
            ->setProduct($product)
            ->setDisplayMinimalPrice($displayMinimalPrice)
            ->setIdSuffix($idSuffix)
            ->toHtml();
    }

    /**
     * Retrieve URL to item Product
     *
     * @param  Supremecreative_Giftregistry_Model_Item|Mage_Catalog_Model_Product $item
     * @param  array $additional
     * @return string
     */
    public function getProductUrl($item, $additional = array())
    {
        if ($item instanceof Mage_Catalog_Model_Product) {
            $product = $item;
        } else {
            $product = $item->getProduct();
        }
        $buyRequest = $item->getBuyRequest();
        if (is_object($buyRequest)) {
            $config = $buyRequest->getSuperProductConfig();
            if ($config && !empty($config['product_id'])) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getStoreId())
                    ->load($config['product_id']);
            }
        }
        return parent::getProductUrl($product, $additional);
    }
}
