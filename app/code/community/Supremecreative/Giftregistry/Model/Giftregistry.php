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
 * Giftregistry model
 *
 * @method Supremecreative_Giftregistry_Model_Resource_Giftregistry _getResource()
 * @method Supremecreative_Giftregistry_Model_Resource_Giftregistry getResource()
 * @method int getShared()
 * @method Supremecreative_Giftregistry_Model_Giftregistry setShared(int $value)
 * @method string getSharingCode()
 * @method Supremecreative_Giftregistry_Model_Giftregistry setSharingCode(string $value)
 * @method string getUpdatedAt()
 * @method Supremecreative_Giftregistry_Model_Giftregistry setUpdatedAt(string $value)
 *
 * @category    Mage
 * @package     Supremecreative_Giftregistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Supremecreative_Giftregistry_Model_Giftregistry extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'giftregistry';
    /**
     * Giftregistry item collection
     *
     * @var Supremecreative_Giftregistry_Model_Mysql4_Item_Collection
     */
    protected $_itemCollection = null;

    /**
     * Store filter for giftregistry
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store = null;

    /**
     * Shared store ids (website stores)
     *
     * @var array
     */
    protected $_storeIds = null;

    /**
     * Entity cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'giftregistry';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('giftregistry/giftregistry');
    }

    /**
     * Load giftregistry by customer
     *
     * @param mixed $customer
     * @param bool $create Create giftregistry if don't exists
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    public function loadByCustomer($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customer = $customer->getId();
        }

        $customer = (int) $customer;
        $this->_getResource()->load($this, $customer, 'customer_id');
        if (!$this->getId()) {
            return false;
        }

        return $this;
    }

    /**
     * Retrieve giftregistry name
     *
     * @return string
     */
    public function getName()
    {
        $name = $this->_getData('name');
        if (!strlen($name)) {
            return Mage::helper('giftregistry')->getDefaultGiftregistryName();
        }
        return $name;
    }

    /**
     * Set random sharing code
     *
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    public function generateSharingCode()
    {
        $this->setSharingCode($this->_getSharingRandomCode());
        return $this;
    }

    /**
     * Load by sharing code
     *
     * @param string $code
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    public function loadByCode($code)
    {
        $this->_getResource()->load($this, $code, 'sharing_code');

        return $this;
    }

    /**
     * Retrieve sharing code (random string)
     *
     * @return string
     */
    protected function _getSharingRandomCode()
    {
        return Mage::helper('core')->uniqHash();
    }

    /**
     * Set date of last update for giftregistry
     *
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
        return $this;
    }

    /**
     * Save related items
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        if (null !== $this->_itemCollection) {
            $this->getItemCollection()->save();
        }
        return $this;
    }

    /**
     * Add catalog product object data to giftregistry
     *
     * @param   Mage_Catalog_Model_Product $product
     * @param   int $qty
     * @param   bool $forciblySetQty
     *
     * @return  Supremecreative_Giftregistry_Model_Item
     */
    protected function _addCatalogProduct(Mage_Catalog_Model_Product $product, $qty = 1, $forciblySetQty = false)
    {
        $item = null;
        foreach ($this->getItemCollection() as $_item) {
            if ($_item->representProduct($product)) {
                $item = $_item;
                break;
            }
        }

        if ($item === null) {
            $storeId = $product->hasGiftregistryStoreId() ? $product->getGiftregistryStoreId() : $this->getStore()->getId();
            $item = Mage::getModel('giftregistry/item');
            $item->setProductId($product->getId())
                ->setGiftregistryId($this->getId())
                ->setAddedAt(now())
                ->setStoreId($storeId)
                ->setOptions($product->getCustomOptions())
                ->setProduct($product)
                ->setQty($qty)
                ->save();

            Mage::dispatchEvent('giftregistry_item_add_after', array('giftregistry' => $this));

            if ($item->getId()) {
                $this->getItemCollection()->addItem($item);
            }
        } else {
            $qty = $forciblySetQty ? $qty : $item->getQty() + $qty;
            $item->setQty($qty)
                ->save();
        }

        $this->addItem($item);

        return $item;
    }

    /**
     * Retrieve giftregistry item collection
     *
     * @return Supremecreative_Giftregistry_Model_Mysql4_Item_Collection
     */
    public function getItemCollection()
    {
        if (is_null($this->_itemCollection)) {
            /** @var $currentWebsiteOnly boolean */
            $currentWebsiteOnly = !Mage::app()->getStore()->isAdmin();
            $this->_itemCollection =  Mage::getResourceModel('giftregistry/item_collection')
                ->addGiftregistryFilter($this)
                ->addStoreFilter($this->getSharedStoreIds($currentWebsiteOnly))
                ->setVisibilityFilter();

            if (Mage::app()->getStore()->isAdmin()) {
                $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
                $this->_itemCollection->setWebsiteId($customer->getWebsiteId());
                $this->_itemCollection->setCustomerGroupId($customer->getGroupId());
            }
        }

        return $this->_itemCollection;
    }

    /**
     * Retrieve giftregistry item collection
     *
     * @param int $itemId
     * @return Supremecreative_Giftregistry_Model_Item
     */
    public function getItem($itemId)
    {
        if (!$itemId) {
            return false;
        }
        return $this->getItemCollection()->getItemById($itemId);
    }

    /**
     * Retrieve Product collection
     *
     * @deprecated after 1.4.2.0
     * @see Supremecreative_Giftregistry_Model_Giftregistry::getItemCollection()
     *
     * @return Supremecreative_Giftregistry_Model_Mysql4_Product_Collection
     */
    public function getProductCollection()
    {
        $collection = $this->getData('product_collection');
        if (is_null($collection)) {
            $collection = Mage::getResourceModel('giftregistry/product_collection');
            $this->setData('product_collection', $collection);
        }
        return $collection;
    }

    /**
     * Adding item to giftregistry
     *
     * @param   Supremecreative_Giftregistry_Model_Item $item
     * @return  Supremecreative_Giftregistry_Model_Giftregistry
     */
    public function addItem(Supremecreative_Giftregistry_Model_Item $item)
    {
        $item->setGiftregistry($this);
        if (!$item->getId()) {
            $this->getItemCollection()->addItem($item);
            Mage::dispatchEvent('giftregistry_add_item', array('item' => $item));
        }
        return $this;
    }

    /**
     * Adds new product to giftregistry.
     * Returns new item or string on error.
     *
     * @param int|Mage_Catalog_Model_Product $product
     * @param mixed $buyRequest
     * @param bool $forciblySetQty
     * @return Supremecreative_Giftregistry_Model_Item|string
     */
    public function addNewItem($product, $buyRequest = null, $forciblySetQty = false)
    {
        /*
         * Always load product, to ensure:
         * a) we have new instance and do not interfere with other products in giftregistry
         * b) product has full set of attributes
         */
        if ($product instanceof Mage_Catalog_Model_Product) {
            $productId = $product->getId();
            // Maybe force some store by giftregistry internal properties
            $storeId = $product->hasGiftregistryStoreId() ? $product->getGiftregistryStoreId() : $product->getStoreId();
        } else {
            $productId = (int) $product;
            if ($buyRequest->getStoreId()) {
                $storeId = $buyRequest->getStoreId();
            } else {
                $storeId = Mage::app()->getStore()->getId();
            }
        }

        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->load($productId);

        if ($buyRequest instanceof Varien_Object) {
            $_buyRequest = $buyRequest;
        } elseif (is_string($buyRequest)) {
            $_buyRequest = new Varien_Object(unserialize($buyRequest));
        } elseif (is_array($buyRequest)) {
            $_buyRequest = new Varien_Object($buyRequest);
        } else {
            $_buyRequest = new Varien_Object();
        }

        $cartCandidates = $product->getTypeInstance(true)
            ->processConfiguration($_buyRequest, $product);

        /**
         * Error message
         */
        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        $errors = array();
        $items = array();

        foreach ($cartCandidates as $candidate) {
            if ($candidate->getParentProductId()) {
                continue;
            }
            $candidate->setGiftregistryStoreId($storeId);

            $qty = $candidate->getQty() ? $candidate->getQty() : 1; // No null values as qty. Convert zero to 1.
            $item = $this->_addCatalogProduct($candidate, $qty, $forciblySetQty);
            $items[] = $item;

            // Collect errors instead of throwing first one
            if ($item->getHasError()) {
                $errors[] = $item->getMessage();
            }
        }

        Mage::dispatchEvent('giftregistry_product_add_after', array('items' => $items));

        return $item;
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    public function setCustomerId($customerId)
    {
        return $this->setData($this->_getResource()->getCustomerIdFieldName(), $customerId);
    }

    /**
     * Retrieve customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData($this->_getResource()->getCustomerIdFieldName());
    }

    /**
     * Retrieve data for save
     *
     * @return array
     */
    public function getDataForSave()
    {
        $data = array();
        $data[$this->_getResource()->getCustomerIdFieldName()] = $this->getCustomerId();
        $data['shared']      = (int) $this->getShared();
        $data['sharing_code']= $this->getSharingCode();
        return $data;
    }

    /**
     * Retrieve shared store ids for current website or all stores if $current is false
     *
     * @param bool $current Use current website or not
     * @return array
     */
    public function getSharedStoreIds($current = true)
    {
        if (is_null($this->_storeIds) || !is_array($this->_storeIds)) {
            if ($current) {
                $this->_storeIds = $this->getStore()->getWebsite()->getStoreIds();
            } else {
                $_storeIds = array();
                $stores = Mage::app()->getStores();
                foreach ($stores as $store) {
                    $_storeIds[] = $store->getId();
                }
                $this->_storeIds = $_storeIds;
            }
        }
        return $this->_storeIds;
    }

    /**
     * Set shared store ids
     *
     * @param array $storeIds
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    public function setSharedStoreIds($storeIds)
    {
        $this->_storeIds = (array) $storeIds;
        return $this;
    }

    /**
     * Retrieve giftregistry store object
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $this->setStore(Mage::app()->getStore());
        }
        return $this->_store;
    }

    /**
     * Set giftregistry store
     *
     * @param Mage_Core_Model_Store $store
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve giftregistry items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->getItemCollection()->getSize();
    }

    /**
     * Retrieve giftregistry has salable item(s)
     *
     * @return bool
     */
    public function isSalable()
    {
        foreach ($this->getItemCollection() as $item) {
            if ($item->getProduct()->getIsSalable()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check customer is owner this giftregistry
     *
     * @param int $customerId
     * @return bool
     */
    public function isOwner($customerId)
    {
        return $customerId == $this->getCustomerId();
    }


    /**
     * Update giftregistry Item and set data from request
     *
     * $params sets how current item configuration must be taken into account and additional options.
     * It's passed to Mage_Catalog_Helper_Product->addParamsToBuyRequest() to compose resulting buyRequest.
     *
     * Basically it can hold
     * - 'current_config', Varien_Object or array - current buyRequest that configures product in this item,
     *   used to restore currently attached files
     * - 'files_prefix': string[a-z0-9_] - prefix that was added at frontend to names of file options (file inputs), so they won't
     *   intersect with other submitted options
     *
     * For more options see Mage_Catalog_Helper_Product->addParamsToBuyRequest()
     *
     * @param int|Supremecreative_Giftregistry_Model_Item $itemId
     * @param Varien_Object $buyRequest
     * @param null|array|Varien_Object $params
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     *
     * @see Mage_Catalog_Helper_Product::addParamsToBuyRequest()
     */
    public function updateItem($itemId, $buyRequest, $params = null)
    {
        $item = null;
        if ($itemId instanceof Supremecreative_Giftregistry_Model_Item) {
            $item = $itemId;
        } else {
            $item = $this->getItem((int)$itemId);
        }
        if (!$item) {
            Mage::throwException(Mage::helper('giftregistry')->__('Cannot specify giftregistry item.'));
        }

        $product = $item->getProduct();
        $productId = $product->getId();
        if ($productId) {
            if (!$params) {
                $params = new Varien_Object();
            } else if (is_array($params)) {
                $params = new Varien_Object($params);
            }
            $params->setCurrentConfig($item->getBuyRequest());
            $buyRequest = Mage::helper('catalog/product')->addParamsToBuyRequest($buyRequest, $params);

            $product->setGiftregistryStoreId($item->getStoreId());
            $items = $this->getItemCollection();
            $isForceSetQuantity = true;
            foreach ($items as $_item) {
                /* @var $_item Supremecreative_Giftregistry_Model_Item */
                if ($_item->getProductId() == $product->getId()
                    && $_item->representProduct($product)
                    && $_item->getId() != $item->getId()) {
                    // We do not add new giftregistry item, but updating the existing one
                    $isForceSetQuantity = false;
                }
            }
            $resultItem = $this->addNewItem($product, $buyRequest, $isForceSetQuantity);
            /**
             * Error message
             */
            if (is_string($resultItem)) {
                Mage::throwException(Mage::helper('checkout')->__($resultItem));
            }

            if ($resultItem->getId() != $itemId) {
                if ($resultItem->getDescription() != $item->getDescription()) {
                    $resultItem->setDescription($item->getDescription())->save();
                }
                $item->isDeleted(true);
                $this->setDataChanges(true);
            } else {
                $resultItem->setQty($buyRequest->getQty() * 1);
                $resultItem->setOrigData('qty', 0);
            }
        } else {
            Mage::throwException(Mage::helper('checkout')->__('The product does not exist.'));
        }
        return $this;
    }

    /**
     * Save giftregistry.
     *
     * @return Supremecreative_Giftregistry_Model_Giftregistry
     */
    public function save()
    {
        $this->_hasDataChanges = true;
        return parent::save();
    }
    
    public function setRegistryInfo(Mage_Customer_Model_Customer $customer, $data) {

        try {
            if ($customer && $data) {

                $this->setCustomerId(trim($customer->getId()));
                $this->setWebsiteId(trim($customer->getWebsiteId()));
                $this->setTypeId( isset($data['type_id']) ? trim($data['type_id']) : '' );
                $this->setEventName( isset($data['event_name']) ? trim($data['event_name']) : '' );
                $this->setEventDate( isset($data['event_date']) ? trim($data['event_date']) : '' );
                $this->setNumberGuests( isset($data['number_guests']) ? trim($data['number_guests']) : '' );
                $this->setEventLocation(isset($data['event_location']) ? trim($data['event_location']) : '');
                $this->setEventMessage( isset($data['event_message']) ? trim($data['event_message']) : '' ); 
                if(!$this->getId()) {
                    $this->setSharingCode($this->_getSharingRandomCode());
                }
        
                if (isset($_FILES['registry_img']['name']) and (file_exists($_FILES['registry_img']['tmp_name']))) {
                    $path = Mage::getBaseDir('media') . DS . 'giftregistryimgs';
                    if (!file_exists($path)) {
                        $imgDirectory = mkdir($path, 0755);
                    } else {
                        $imgDirectory = true;
                    }
                    
                    if($imgDirectory) {

                        $uploader = new Varien_File_Uploader('registry_img');
                        if($uploader) {

                            $uploader->setAllowedExtensions(array('png', 'jpeg', 'jpg'));
                            $uploader->setAllowRenameFiles(false);
                            $uploader->setFilesDispersion(false);
                            
                            if($this->getId()) {
                               if(file_exists($path . DS . $this->getBannerImg())) unlink($path . DS . $this->getBannerImg());
                            }
                            
                            $uploader->save($path, $this->getSharingCode() . '.' . pathinfo($_FILES['registry_img']['name'], PATHINFO_EXTENSION)); 
                            $this->setBannerImg($this->getSharingCode() .  '.' . pathinfo($_FILES['registry_img']['name'], PATHINFO_EXTENSION)); 

                        }

                    }                     

                }                  

                $shipping = [];
                $shipping["firstname"] = isset($data['firstname']) ? trim($data['firstname']) : '';
                $shipping["middlename"] = isset($data['middlename']) ? trim($data['middlename']) : '';                
                $shipping["lastname"] = isset($data['lastname']) ? trim($data['lastname']) : '';
                $shipping["telephone"] = isset($data['telephone']) ? trim($data['telephone']) : '';  
                $shipping["street_1"] = isset($data['street_1']) ? trim($data['street_1']) : '';
                $shipping["street_2"] = isset($data['street_2']) ? trim($data['street_2']) : '';  
                $shipping["city"] = isset($data['city']) ? trim($data['city']) : '';
                $shipping["region_id"] = isset($data['region_id']) ? $data['region_id'] : '';  
                $shipping["region"] = isset($data['region']) ? trim($data['region']) : '';
                $shipping["postcode"] = isset($data['postcode']) ? trim($data['postcode']) : '';  
                $shipping["country_id"] = isset($data['country_id']) ? $data['country_id'] : ''; 

                $this->setShipping(json_encode($shipping));
            } else {
                throw new Exception("Error Processing Request: Insufficient Data Provided");
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    
    protected function validateNotEmpty() {
        
        $errors = array();
        
        $nonemptyValidator = new Zend_Validate_NotEmpty(); 
        
        $shipping = json_decode($this->getShipping(), true);        
        
        if (!$nonemptyValidator->isValid($this->getEventName())) {
          $errors[] = Mage::helper('giftregistry')->__('Please provide an The Event Name.');  
        } 

        if (!$nonemptyValidator->isValid($this->getTypeId())) {
            $errors[] = Mage::helper('giftregistry')->__('Please provide an The Event Type.');
        }                   
       
        if (!$nonemptyValidator->isValid($shipping["firstname"])) {
            $errors[] = Mage::helper('giftregistry')->__('Please provide your First Name.');
        }           
        
        if (!$nonemptyValidator->isValid($shipping["lastname"])) {
            $errors[] = Mage::helper('giftregistry')->__('Please provide your Last Name.');
        }          
        
        if (!$nonemptyValidator->isValid($shipping["telephone"])) {
            $errors[] = Mage::helper('giftregistry')->__('Please provide your Telephone Number.');
        }      
        if (!$nonemptyValidator->isValid($shipping["street_1"])) {
            $errors[] = Mage::helper('giftregistry')->__('Please provide the street address.');
        } 
        
        if (!$nonemptyValidator->isValid($shipping["region_id"]) && !$nonemptyValidator->isValid($shipping["region"])) {
            $errors[] = Mage::helper('giftregistry')->__('Please provide the State\\Province name.');
        }  
        
        if (!$nonemptyValidator->isValid($shipping["postcode"])) {
            $errors[] = Mage::helper('giftregistry')->__('Please provide the Postcode.');
        }        
  
        if (!$nonemptyValidator->isValid($shipping["country_id"])) {
            $errors[] = Mage::helper('giftregistry')->__('Please provide the Country name.');
        }  
                        
       if (!$nonemptyValidator->isValid($shipping["city"])) {
            $errors[] = Mage::helper('giftregistry')->__('Please provide the City name.');
        } 
        
        return $errors;
                
    }

    public function validate() {
        
        $errors = $this->validateNotEmpty();
        $digitValidator = new Zend_Validate_Digits();
        $alphaValidator = new Zend_Validate_Alpha(array('allowWhiteSpace' => true));        
        $shipping = json_decode($this->getShipping(), true);              
 
        if(!$alphaValidator->isValid($shipping["city"])) {
            $errors[] = Mage::helper('giftregistry')->__('The City name should have alphabetic only characters');
        }  

        if($shipping["region_id"]) {
            if(!$digitValidator->isValid($shipping["region_id"])) {
                $errors[] = Mage::helper('giftregistry')->__('The region ID should have digit only characters');
            }              
        }

        if(!$alphaValidator->isValid($shipping["country_id"])) {
            $errors[] = Mage::helper('giftregistry')->__('The Country name should have alphabetic only characters');
        }                
        
        return $errors;
    } 
    
 
}
