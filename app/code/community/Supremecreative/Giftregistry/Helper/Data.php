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
 * Giftregistry Data Helper
 *
 * @category   Mage
 * @package    Supremecreative_Giftregistry
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Supremecreative_Giftregistry_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config key 'Display Giftregistry Summary'
     */
    const XML_PATH_GIFTREGISTRY_LINK_USE_QTY = 'giftregistry/giftregistry_link/use_qty';

    /**
     * Config key 'Display Out of Stock Products'
     */
    const XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK = 'cataloginventory/options/show_out_of_stock';

    /**
     * Currently logged in customer
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_currentCustomer = null;

    /**
     * Customer Giftregistry instance
     *
     * @var Supremecreative_Giftregistry_Model_Giftregistry
     */
    protected $_giftregistry = null;
    
    /**
     * Giftregistry Type instance
     *
     * @var Supremecreative_Giftregistry_Model_Type
     */
    protected $_giftregistrytype = null;

    /**
     * Giftregistry Product Items Collection
     *
     * @var Supremecreative_Giftregistry_Model_Mysql4_Product_Collection
     */
    protected $_productCollection = null;

    /**
     * Giftregistry Items Collection
     *
     * @var Supremecreative_Giftregistry_Model_Resource_Item_Collection
     */
    protected $_giftregistryItemCollection = null;

    /**
     * Retreive customer session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Retrieve customer login status
     *
     * @return bool
     */
    protected function _isCustomerLogIn()
    {
        return $this->_getCustomerSession()->isLoggedIn();
    }

    /**
     * Retrieve logged in customer
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCurrentCustomer()
    {
        return $this->getCustomer();
    }

    /**
     * Set current customer
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    public function setCustomer(Mage_Customer_Model_Customer $customer)
    {
        $this->_currentCustomer = $customer;
    }

    /**
     * Retrieve current customer
     *
     * @return Mage_Customer_Model_Customer|null
     */
    public function getCustomer()
    {
        if (!$this->_currentCustomer && $this->_getCustomerSession()->isLoggedIn()) {
            $this->_currentCustomer = $this->_getCustomerSession()->getCustomer();
        }
        return $this->_currentCustomer;
    }

    /**
     * Retrieve giftregistry by logged in customer
     *
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    public function getGiftregistry()
    {
        if (is_null($this->_giftregistry)) {
            if (Mage::registry('shared_giftregistry')) {
                $this->_giftregistry = Mage::registry('shared_giftregistry');
            } elseif (Mage::registry('giftregistry')) {
                $this->_giftregistry = Mage::registry('giftregistry');
            } else {
                $this->_giftregistry = Mage::getModel('giftregistry/giftregistry');
                if ($this->getCustomer()) {
                    $this->_giftregistry->loadByCustomer($this->getCustomer());
                    Mage::register('giftregistry', $this->_giftregistry);
                }
            }
        }
        return $this->_giftregistry;
    }
    
    
    public function getGiftregistryShipping() 
    {
        
        $giftregistryShipping = json_decode($this->getGiftregistry()->getShipping(), true); 
        
        if($giftregistryShipping) {
            return $giftregistryShipping;
        } else {
            return false;
        }
        
    }

    /**
     * Retrieve giftregistry items availability
     *
     * @deprecated after 1.6.0.0
     *
     * @return bool
     */
    public function hasItems()
    {
        return $this->getGiftregistry()->getItemsCount() > 0;
    }

    /**
     * Retrieve giftregistry item count (include config settings)
     * Used in top link menu only
     *
     * @return int
     */
    public function getItemCount()
    {
        $storedDisplayType = $this->_getCustomerSession()->getGiftregistryDisplayType();
        $currentDisplayType = Mage::getStoreConfig(self::XML_PATH_GIFTREGISTRY_LINK_USE_QTY);

        $storedDisplayOutOfStockProducts = $this->_getCustomerSession()->getDisplayOutOfStockProducts();
        $currentDisplayOutOfStockProducts = Mage::getStoreConfig(self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK);
        if (!$this->_getCustomerSession()->hasGiftregistryItemCount()
                || ($currentDisplayType != $storedDisplayType)
                || $this->_getCustomerSession()->hasDisplayOutOfStockProducts()
                || ($currentDisplayOutOfStockProducts != $storedDisplayOutOfStockProducts)) {
            $this->calculate();
        }

        return $this->_getCustomerSession()->getGiftregistryItemCount();
    }

    /**
     * Retrieve giftregistry product items collection
     *
     * alias for getProductCollection
     *
     * @deprecated after 1.4.2.0
     * @see Supremecreative_Giftregistry_Model_Giftregistry::getItemCollection()
     *
     * @return Supremecreative_Giftregistry_Model_Mysql4_Product_Collection
     */
    public function getItemCollection()
    {
        return $this->getProductCollection();
    }

    /**
     * Create giftregistry item collection
     *
     * @return Supremecreative_Giftregistry_Model_Resource_Item_Collection
     */
    protected function _createGiftregistryItemCollection()
    {
        return $this->getGiftregistry()->getItemCollection();
    }

    /**
     * Retrieve giftregistry items collection
     *
     * @return Supremecreative_Giftregistry_Model_Resource_Item_Collection
     */
    public function getGiftregistryItemCollection()
    {
        if (is_null($this->_giftregistryItemCollection)) {
            $this->_giftregistryItemCollection = $this->_createGiftregistryItemCollection();
        }
        return $this->_giftregistryItemCollection;
    }

    /**
     * Retrieve giftregistry product items collection
     *
     * @deprecated after 1.4.2.0
     * @see Supremecreative_Giftregistry_Model_Giftregistry::getItemCollection()
     *
     * @return Supremecreative_Giftregistry_Model_Mysql4_Product_Collection
     */
    public function getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = $this->getGiftregistry()
                ->getProductCollection();

            Mage::getSingleton('catalog/product_visibility')
                ->addVisibleInSiteFilterToCollection($this->_productCollection);
        }
        return $this->_productCollection;
    }

    /**
     * Retrieve Item Store for URL
     *
     * @param Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     * @return Mage_Core_Model_Store
     */
    protected function _getUrlStore($item)
    {
        $storeId = null;
        $product = null;
        if ($item instanceof Supremecreative_Giftregistry_Model_Item) {
            $product = $item->getProduct();
        } elseif ($item instanceof Mage_Catalog_Model_Product) {
            $product = $item;
        }
        if ($product) {
            if ($product->isVisibleInSiteVisibility()) {
                $storeId = $product->getStoreId();
            } else if ($product->hasUrlDataObject()) {
                $storeId = $product->getUrlDataObject()->getStoreId();
            }
        }
        return Mage::app()->getStore($storeId);
    }

    /**
     * Retrieve URL for removing item from giftregistry
     *
     * @param Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     * @return string
     */
    public function getRemoveUrl($item)
    {
        return $this->_getUrl('giftregistry/index/remove',
            array(
                'item' => $item->getGiftregistryItemId(),
                Mage_Core_Model_Url::FORM_KEY => $this->_getSingletonModel('core/session')->getFormKey()
            )
        );
    }

    /**
     * Retrieve URL for removing item from giftregistry
     *
     * @param Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     * @return string
     */
    public function getConfigureUrl($item)
    {
        return $this->_getUrl('giftregistry/index/configure', array(
            'item' => $item->getGiftregistryItemId()
        ));
    }

    /**
     * Retrieve url for adding product to giftregistry
     *
     * @param Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     *
     * @return  string|bool
     */
    public function getAddUrl($item)
    {
        return $this->getAddUrlWithParams($item);
    }

    /**
     * Retrieve url for adding product to giftregistry
     *
     * @param int $itemId
     *
     * @return  string
     */
    public function getMoveFromCartUrl($itemId)
    {
        return $this->_getUrl('giftregistry/index/fromcart', array('item' => $itemId));
    }

    /**
     * Retrieve url for updating product in giftregistry
     *
     * @param Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     *
     * @return  string|bool
     */
    public function getUpdateUrl($item)
    {
        $itemId = null;
        if ($item instanceof Mage_Catalog_Model_Product) {
            $itemId = $item->getGiftregistryItemId();
        }
        if ($item instanceof Supremecreative_Giftregistry_Model_Item) {
            $itemId = $item->getId();
        }

        if ($itemId) {
            return $this->_getUrl('giftregistry/index/updateItemOptions', array('id' => $itemId));
        }

        return false;
    }

    /**
     * Retrieve url for adding product to giftregistry with params
     *
     * @param Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     * @param array $params
     *
     * @return  string|bool
     */
    public function getAddUrlWithParams($item, array $params = array())
    {
        $productId = null;
        if ($item instanceof Mage_Catalog_Model_Product) {
            $productId = $item->getEntityId();
        }
        if ($item instanceof Supremecreative_Giftregistry_Model_Item) {
            $productId = $item->getProductId();
        }

        if ($productId) {
            $params['product'] = $productId;
            $params[Mage_Core_Model_Url::FORM_KEY] = $this->_getSingletonModel('core/session')->getFormKey();
            return $this->_getUrlStore($item)->getUrl('giftregistry/index/add', $params);
        }

        return false;
    }

    /**
     * Retrieve URL for adding item to shopping cart
     *
     * @param string|Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     * @return  string
     */
    public function getAddToCartUrl($item)
    {
        $continueUrl  = $this->_getHelperInstance('core')->urlEncode(
            $this->_getUrl('*/*/*', array(
                '_current'      => true,
                '_use_rewrite'  => true,
                '_store_to_url' => true,
            ))
        );
        $params = array(
            'item' => is_string($item) ? $item : $item->getGiftregistryItemId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $continueUrl,
            Mage_Core_Model_Url::FORM_KEY => $this->_getSingletonModel('core/session')->getFormKey()
        );
        return $this->_getUrlStore($item)->getUrl('giftregistry/index/cart', $params);
    }

    /**
     * Return helper instance
     *
     * @param string $helperName
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelperInstance($helperName)
    {
        return Mage::helper($helperName);
    }

    /**
     * Return model instance
     *
     * @param string $className
     * @param array $arguments
     * @return Mage_Core_Model_Abstract
     */
    protected function _getSingletonModel($className, $arguments = array())
    {
        return Mage::getSingleton($className, $arguments);
    }

    /**
     * Retrieve URL for adding item to shoping cart from shared giftregistry
     *
     * @param string|Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     * @return  string
     */
    public function getSharedAddToCartUrl($item)
    {
        $continueUrl  = Mage::helper('core')->urlEncode(Mage::getUrl('*/*/*', array(
            '_current'      => true,
            '_use_rewrite'  => true,
            '_store_to_url' => true,
        )));

        $params = array(
            'item' => is_string($item) ? $item : $item->getGiftregistryItemId(),
            'code' => $this->getGiftregistry()->getSharingCode(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $continueUrl
        );
        return $this->_getUrlStore($item)->getUrl('giftregistry/shared/cart', $params);
    }

    /**
     * Retrieve url for adding item to shoping cart with b64 referer
     *
     * @deprecated
     * @param   Mage_Catalog_Model_Product|Supremecreative_Giftregistry_Model_Item $item
     * @return  string
     */
    public function getAddToCartUrlBase64($item)
    {
        return $this->getAddToCartUrl($item);
    }

    /**
     * Retrieve customer giftregistry url
     *
     * @param int $giftregistryId
     * @return string
     */
    public function getListUrl($giftregistryId = null)
    {
        $params = array();
        if ($giftregistryId) {
            $params['giftregistry_id'] = $giftregistryId;
        }
        return $this->_getUrl('giftregistry', $params);
    }

    /**
     * Check is allow giftregistry module
     *
     * @return bool
     */
    public function isAllow()
    {
        if ($this->isModuleOutputEnabled() && Mage::getStoreConfig('giftregistry/general/active')) {
            return true;
        }
        return false;
    }

    /**
     * Check is allow giftregistry action in shopping cart
     *
     * @return bool
     */
    public function isAllowInCart()
    {
        return $this->isAllow() && $this->getCustomer();
    }

    /**
     * Retrieve customer name
     *
     * @return string|null
     */
    public function getCustomerName()
    {
        $customer = $this->_getCurrentCustomer();
        if ($customer) {
            return $customer->getName();
        }
    }

    /**
     * Retrieve RSS URL
     *
     * @param $giftregistryId
     * @return string
     */
    public function getRssUrl($giftregistryId = null)
    {
        $customer = $this->_getCurrentCustomer();
        if ($customer) {
            $key = $customer->getId() . ',' . $customer->getEmail();
            $params = array(
                'data' => Mage::helper('core')->urlEncode($key),
                '_secure' => false,
            );
        }
        if ($giftregistryId) {
            $params['giftregistry_id'] = $giftregistryId;
        }
        return $this->_getUrl(
            'rss/index/giftregistry',
            $params
        );
    }

    /**
     * Is allow RSS
     *
     * @return bool
     */
    public function isRssAllow()
    {
        return Mage::getStoreConfigFlag('rss/giftregistry/active');
    }

    /**
     * Retrieve default empty comment message
     *
     * @return string
     */
    public function defaultCommentString()
    {
        return $this->__('Please, enter your comments...');
    }

    /**
     * Retrieve default empty comment message
     *
     * @return string
     */
    public function getDefaultGiftregistryName()
    {
        return $this->__('Giftregistry');
    }

    /**
     * Calculate count of giftregistry items and put value to customer session.
     * Method called after giftregistry modifications and trigger 'giftregistry_items_renewed' event.
     * Depends from configuration.
     *
     * @return Supremecreative_Giftregistry_Helper_Data
     */
    public function calculate()
    {
        $session = $this->_getCustomerSession();
        $count = 0;
        if ($this->getCustomer()) {
            $collection = $this->getGiftregistryItemCollection()->setInStockFilter(true);
            if (Mage::getStoreConfig(self::XML_PATH_GIFTREGISTRY_LINK_USE_QTY)) {
                $count = $collection->getItemsQty();
            } else {
                $count = $collection->getSize();
            }
            $session->setGiftregistryDisplayType(Mage::getStoreConfig(self::XML_PATH_GIFTREGISTRY_LINK_USE_QTY));
            $session->setDisplayOutOfStockProducts(
                Mage::getStoreConfig(self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK)
            );
        }
        $session->setGiftregistryItemCount($count);
        Mage::dispatchEvent('giftregistry_items_renewed');
        return $this;
    }

    /**
     * Should display item quantities in my gift registry link
     *
     * @return bool
     */
    public function isDisplayQty()
    {
        return Mage::getStoreConfig(self::XML_PATH_GIFTREGISTRY_LINK_USE_QTY);
    }
    
    public function getEventTypes() {
        $collection = Mage::getModel('giftregistry/type')->getCollection();
        return $collection;
    }    
    
    public function getDefaultShippingAddress() {

        $customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();

        if ($customerAddressId){
            return Mage::getModel('customer/address')->load($customerAddressId);
        }    

        return false;

    }
    
    public function getNameBlockHtml()
    {
        return Mage::getBlockSingleton('customer/address_edit')->getNameBlockHtml();
    }    
     
    public function getAddressEditBlock()
    {
        return Mage::getBlockSingleton('customer/address_edit');
    }    
    
    
    /**
     * Retrieve giftregistry type
     *
     * @return Supremecreative_Giftregistry_Model_Type
     */
    public function getGiftregistryType()
    {
        if (is_null($this->_giftregistrytype)) {
            $giftregistryTypeId = $this->getGiftregistry()->getTypeId();
            if($giftregistryTypeId){
                return Mage::getModel('giftregistry/type')->load($giftregistryTypeId); 
            }
        }
        return false;
    }    
    
    
    /**
     * Retrieve giftregistry name
     *
     * @return Supremecreative_Giftregistry_Model_Type
     */
    public function getGiftRegistryName()
    {
        return $this->getGiftregistry()->getEventName() ? $this->getGiftregistry()->getEventName() : null;
    }
    
    public function getGiftRegistryMessage() 
    {
        return $this->getGiftregistry()->getEventMessage() ? $this->getGiftregistry()->getEventMessage() : null;   
    }
    
    public function getGiftRegistryImageUrl() 
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)  . 'giftregistryimgs/' . $this->getGiftregistry()->getBannerImg() ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)  . 'giftregistryimgs/' . $this->getGiftregistry()->getBannerImg() : '';
    }  
    
    public function getGiftRegistryUrl() 
    {
        return Mage::getBaseUrl()  . 'giftregistry/shared/index/code/' . $this->getGiftregistry()->getBannerImg() ? Mage::getBaseUrl()  . 'giftregistry/shared/index/code/' . $this->getGiftregistry()->getBannerImg() : '';
    }
    
    
}
