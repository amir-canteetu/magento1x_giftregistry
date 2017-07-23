<?php

/**
 * Giftregistry front controller
 *
 * @category    Supremecreative
 * @package     Supremecreative_Giftregistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Supremecreative_Giftregistry_IndexController extends Supremecreative_Giftregistry_Controller_Abstract
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * If true, authentication in this controller (giftregistry) could be skipped
     *
     * @var bool
     */
    protected $_skipAuthentication = false;

    /**
     * Extend preDispatch
     *
     * @return Mage_Core_Controller_Front_Action|void
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $session = Mage::getSingleton('customer/session');  
        if (!$session->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }  

        $requestParams = $this->getRequest()->getParams();
        $fullActionName = $this->getFullActionName();
        if(isset($requestParams['product']) && $fullActionName == 'giftregistry_index_add') {
            if(!$session->getAddToGiftregistryRequest()) {
               $session->setAddToGiftregistryRequest($requestParams); 
            } 
            if(!$session->getBeforeGiftregistryUrl()) {
               $session->setBeforeGiftregistryUrl($this->_getRefererUrl()); 
            }            
        }
                       
        if (!Mage::getStoreConfigFlag('giftregistry/general/active')) {
            $this->norouteAction();
            return;
        }
    }

    /**
     * Set skipping authentication in actions of this controller (giftregistry)
     *
     * @return Supremecreative_Giftregistry_IndexController
     */
    public function skipAuthentication()
    {
        $this->_skipAuthentication = true;
        return $this;
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
     * Display customer giftregistry
     *
     * @return Supremecreative_Giftregistry_IndexController
     */
    public function indexAction()
    {
        $session = Mage::getSingleton('customer/session');  
        if (!$this->_getGiftRegistry()) {
            $session->addNotice(Mage::helper('giftregistry')->__('Please create a gift registry'));
            return $this->_redirect('*/*/new/');
        }    
        
        if($session->getAddToGiftregistryRequest()) {
           return $this->_addItemToGiftregistry(); 
        } 
            
        $this->loadLayout();
        
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('giftregistry/session');        
        
        $this->renderLayout();
        return $this;        

    }
    
    
    /**
     * Show new-gift-registry page
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */    
    public function newAction() 
    {
        $this->loadLayout();
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
         if ($navigationBlock) {
            $navigationBlock->setActive('giftregistry/index');
        }       
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('giftregistry/session');          
        $this->renderLayout();
        return $this;
    }    

    /**
     * Adding new item to giftregistry
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */
    public function addAction()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if(!$this->_validateFormKey() || !$productId ) {
            return $this->_redirect('*/*');
        }
        
        $session = Mage::getSingleton('customer/session');  

        $giftregistry = $this->_getGiftRegistry();  
        if (!$giftregistry) {
            $session->addNotice(Mage::helper('giftregistry')->__('Please create a gift registry first'));            
            return $this->_redirect('*/*/new/');
        }         

        $this->_addItemToGiftRegistry();
    }

    /**
     * Add the item to gift registry
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */
    protected function _addItemToGiftregistry()
    {        
        $session = Mage::getSingleton('customer/session');  
        
        $giftregistry = $this->_getGiftRegistry();         
        $requestParams = $this->getRequest()->getParams();
        $productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;

        if (!$productId) {
            if ($session->getAddToGiftregistryRequest()) {
                $requestParams = $session->getAddToGiftregistryRequest();
                $productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;
                $session->unsAddToGiftregistryRequest();
            }  else{
                return $this->_redirect('*/');              
            }             
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_redirect('*/');
            return;
        }
        
        //If product is configurable, check if options have been set. If not, don't add to giftregistry
        $buyRequest = new Varien_Object($requestParams);
        $attributes = $buyRequest->getSuperAttribute();
        if (is_array($attributes)) {
            foreach ($attributes as $key => $val) {
                if (empty($val)) {
                    unset($attributes[$key]);
                }
            }
        }   
        
        if ($product->isConfigurable() && empty($attributes)) {
            Mage::getSingleton('core/session')->addNotice('Please specify the product\'s option(s).');
            return $this->_redirectReferer();
        } 
           
        try {

            $result = $giftregistry->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $giftregistry->save();

            Mage::dispatchEvent(
                'giftregistry_add_product',
                array(
                    'giftregistry' => $giftregistry,
                    'product' => $product,
                    'item' => $result
                )
            );

            $referer = $session->getBeforeGiftregistryUrl();
            Mage::helper('giftregistry')->calculate();

            if ($referer) {
                $message = $this->__('%1$s Has Been Added To Your Gift Registry. Click <a href="%2$s">Here</a> To Continue Shopping.',
                    $product->getName(), Mage::helper('core')->escapeUrl($referer));  
                    $session->setBeforeGiftregistryUrl(null);
                    $session->setAddToGiftregistryRequest(null);
            } else {
                $message = $this->__( '%1$s Has Been Added To Your Gift Registry.', $product->getName() );                
            }
            $session->addSuccess($message);
            
        } catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to giftregistry: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to giftregistry.'));
        }

        $this->_redirect('*', array('giftregistry_id' => $giftregistry->getId()));
    }

    /**
     * Action to reconfigure giftregistry item
     */
    public function configureAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        try {
            /* @var $item Supremecreative_Giftregistry_Model_Item */
            $item = Mage::getModel('giftregistry/item');
            $item->loadWithOptions($id);
            if (!$item->getId()) {
                Mage::throwException($this->__('Cannot load giftregistry item'));
            }
            $giftregistry = $this->_getGiftregistry($item->getGiftregistryId());
            if (!$giftregistry) {
                return $this->norouteAction();
            }

            Mage::register('giftregistry_item', $item);

            $params = new Varien_Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $buyRequest = $item->getBuyRequest();
            if (!$buyRequest->getQty() && $item->getQty()) {
                $buyRequest->setQty($item->getQty());
            }
            if ($buyRequest->getQty() && !$item->getQty()) {
                $item->setQty($buyRequest->getQty());
                Mage::helper('giftregistry')->calculate();
            }
            $params->setBuyRequest($buyRequest);
            Mage::helper('catalog/product_view')->prepareAndRender($item->getProductId(), $this, $params);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('customer/session')->addError($e->getMessage());
            $this->_redirect('*');
            return;
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('Cannot configure product'));
            Mage::logException($e);
            $this->_redirect('*');
            return;
        }
    }

    /**
     * Action to accept new configuration for a giftregistry item
     */
    public function updateItemOptionsAction()
    {
        $session = Mage::getSingleton('customer/session');
        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_redirect('*/');
            return;
        }

        try {
            $id = (int) $this->getRequest()->getParam('id');
            /* @var Supremecreative_Giftregistry_Model_Item */
            $item = Mage::getModel('giftregistry/item');
            $item->load($id);
            $giftregistry = $this->_getGiftregistry($item->getGiftregistryId());
            if (!$giftregistry) {
                $this->_redirect('*/');
                return;
            }

            $buyRequest = new Varien_Object($this->getRequest()->getParams());

            $giftregistry->updateItem($id, $buyRequest)
                ->save();

            Mage::helper('giftregistry')->calculate();
            Mage::dispatchEvent('giftregistry_update_item', array(
                'giftregistry' => $giftregistry, 'product' => $product, 'item' => $giftregistry->getItem($id))
            );

            Mage::helper('giftregistry')->calculate();

            $message = $this->__('%1$s has been updated in your giftregistry.', $product->getName());
            $session->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addError($this->__('An error occurred while updating giftregistry.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*', array('giftregistry_id' => $giftregistry->getId()));
    }

    
    /**
     * Show giftregistry edit form
     */    
    public function editAction() 
    {

        $session = Mage::getSingleton('customer/session');
        if (!$this->_getGiftRegistry()) {
            $session->addNotice(Mage::helper('giftregistry')->__('Please create a gift registry'));
            return $this->_redirect('*/*/new/');
        }

        $this->loadLayout();

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('giftregistry/session');

        $this->renderLayout();
        return $this;
    }
    
    /**
     * Show giftregistry edit form
     */    
    public function editPostAction() 
    {

        $session = Mage::getSingleton('customer/session');
        if (!$this->_getGiftRegistry()) {
            $session->addNotice(Mage::helper('giftregistry')->__('Please create a gift registry'));
            return $this->_redirect('*/*/new/');
        }

        $this->loadLayout();

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('giftregistry/session');

        $this->renderLayout();
        return $this;
    }    
    

    /**
     * Update giftregistry item comments
     */
    public function updateAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }
        $giftregistry = $this->_getGiftregistry();
        if (!$giftregistry) {
            return $this->norouteAction();
        }

        $post = $this->getRequest()->getPost();
        if ($post && isset($post['description']) && is_array($post['description'])) {
            $updatedItems = 0;

            foreach ($post['description'] as $itemId => $description) {
                $item = Mage::getModel('giftregistry/item')->load($itemId);
                if ($item->getGiftregistryId() != $giftregistry->getId()) {
                    continue;
                }

                // Extract new values
                $description = (string)$description;

                if ($description == Mage::helper('giftregistry')->defaultCommentString()) {
                    $description = '';
                } elseif (!strlen($description)) {
                    $description = $item->getDescription();
                }

                $qty = null;
                if (isset($post['qty'][$itemId])) {
                    $qty = $this->_processLocalizedQty($post['qty'][$itemId]);
                }
                if (is_null($qty)) {
                    $qty = $item->getQty();
                    if (!$qty) {
                        $qty = 1;
                    }
                } elseif (0 == $qty) {
                    try {
                        $item->delete();
                    } catch (Exception $e) {
                        Mage::logException($e);
                        Mage::getSingleton('customer/session')->addError(
                            $this->__('Can\'t delete item from giftregistry')
                        );
                    }
                }

                // Check that we need to save
                if (($item->getDescription() == $description) && ($item->getQty() == $qty)) {
                    continue;
                }
                try {
                    $item->setDescription($description)
                        ->setQty($qty)
                        ->save();
                    $updatedItems++;
                } catch (Exception $e) {
                    Mage::getSingleton('customer/session')->addError(
                        $this->__('Can\'t save description %s', Mage::helper('core')->escapeHtml($description))
                    );
                }
            }

            // save giftregistry model for setting date of last update
            if ($updatedItems) {
                try {
                    $giftregistry->save();
                    Mage::helper('giftregistry')->calculate();
                } catch (Exception $e) {
                    Mage::getSingleton('customer/session')->addError($this->__('Can\'t update giftregistry'));
                }
            }

            if (isset($post['save_and_share'])) {
                $this->_redirect('*/*/share', array('giftregistry_id' => $giftregistry->getId()));
                return;
            }
        }
        $this->_redirect('*', array('giftregistry_id' => $giftregistry->getId()));
    }

    /**
     * Remove item
     */
    public function removeAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*');
        }
        $id = (int) $this->getRequest()->getParam('item');
        $item = Mage::getModel('giftregistry/item')->load($id);
        if (!$item->getId()) {
            return $this->norouteAction();
        }
        $giftregistry = $this->_getGiftregistry($item->getGiftregistryId());
        if (!$giftregistry) {
            return $this->norouteAction();
        }
        try {
            $item->delete();
            $giftregistry->save();
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('customer/session')->addError(
                $this->__('An error occurred while deleting the item from giftregistry: %s', $e->getMessage())
            );
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError(
                $this->__('An error occurred while deleting the item from giftregistry.')
            );
        }

        Mage::helper('giftregistry')->calculate();

        $this->_redirectReferer(Mage::getUrl('*/*'));
    }

    /**
     * Add giftregistry item to shopping cart and remove from giftregistry
     *
     * If Product has required options - item removed from giftregistry and redirect
     * to product view page with message about needed defined required options
     */
    public function cartAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*');
        }
        $itemId = (int) $this->getRequest()->getParam('item');

        /* @var $item Supremecreative_Giftregistry_Model_Item */
        $item = Mage::getModel('giftregistry/item')->load($itemId);
        if (!$item->getId()) {
            return $this->_redirect('*/*');
        }
        $giftregistry = $this->_getGiftregistry($item->getGiftregistryId());
        if (!$giftregistry) {
            return $this->_redirect('*/*');
        }

        // Set qty
        $qty = $this->getRequest()->getParam('qty');
        if (is_array($qty)) {
            if (isset($qty[$itemId])) {
                $qty = $qty[$itemId];
            } else {
                $qty = 1;
            }
        }
        $qty = $this->_processLocalizedQty($qty);
        if ($qty) {
            $item->setQty($qty);
        }

        /* @var $session Supremecreative_Giftregistry_Model_Session */
        $session    = Mage::getSingleton('giftregistry/session');
        $cart       = Mage::getSingleton('checkout/cart');

        $redirectUrl = Mage::getUrl('*/*');

        try {
            $options = Mage::getModel('giftregistry/item_option')->getCollection()
                    ->addItemFilter(array($itemId));
            $item->setOptions($options->getOptionsByItem($itemId));

            $buyRequest = Mage::helper('catalog/product')->addParamsToBuyRequest(
                $this->getRequest()->getParams(),
                array('current_config' => $item->getBuyRequest())
            );

            $item->mergeBuyRequest($buyRequest);
            if ($item->addToCart($cart, true)) {
                $cart->save()->getQuote()->collectTotals();
            }

            $giftregistry->save();
            Mage::helper('giftregistry')->calculate();

            if (Mage::helper('checkout/cart')->getShouldRedirectToCart()) {
                $redirectUrl = Mage::helper('checkout/cart')->getCartUrl();
            }
            Mage::helper('giftregistry')->calculate();

            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($item->getProductId());
            $productName = Mage::helper('core')->escapeHtml($product->getName());
            $message = $this->__('%s was added to your shopping cart.', $productName);
            Mage::getSingleton('catalog/session')->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Supremecreative_Giftregistry_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                $session->addError($this->__('This product(s) is currently out of stock'));
            } else if ($e->getCode() == Supremecreative_Giftregistry_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                Mage::getSingleton('catalog/session')->addNotice($e->getMessage());
                $redirectUrl = Mage::getUrl('*/*/configure/', array('id' => $item->getId()));
            } else {
                Mage::getSingleton('catalog/session')->addNotice($e->getMessage());
                $redirectUrl = Mage::getUrl('*/*/configure/', array('id' => $item->getId()));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $session->addException($e, $this->__('Cannot add item to shopping cart'));
        }

        Mage::helper('giftregistry')->calculate();

        return $this->_redirectUrl($redirectUrl);
    }

    /**
     * Add cart item to giftregistry and remove from cart
     */
    public function fromcartAction()
    {
        $giftregistry = $this->_getGiftregistry();
        if (!$giftregistry) {
            return $this->norouteAction();
        }
        $itemId = (int) $this->getRequest()->getParam('item');

        /* @var Mage_Checkout_Model_Cart $cart */
        $cart = Mage::getSingleton('checkout/cart');
        $session = Mage::getSingleton('checkout/session');

        try {
            $item = $cart->getQuote()->getItemById($itemId);
            if (!$item) {
                Mage::throwException(
                    Mage::helper('giftregistry')->__("Requested cart item doesn't exist")
                );
            }

            $productId  = $item->getProductId();
            $buyRequest = $item->getBuyRequest();

            $giftregistry->addNewItem($productId, $buyRequest);

            $productIds[] = $productId;
            $cart->getQuote()->removeItem($itemId);
            $cart->save();
            Mage::helper('giftregistry')->calculate();
            $productName = Mage::helper('core')->escapeHtml($item->getProduct()->getName());
            $giftregistryName = Mage::helper('core')->escapeHtml($giftregistry->getName());
            $session->addSuccess(
                Mage::helper('giftregistry')->__("%s has been moved to giftregistry %s", $productName, $giftregistryName)
            );
            $giftregistry->save();
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('giftregistry')->__('Cannot move item to giftregistry'));
        }

        return $this->_redirectUrl(Mage::helper('checkout/cart')->getCartUrl());
    }

    /**
     * Prepare giftregistry for share
     */
    public function shareAction()
    {
        $this->_getGiftregistry();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('giftregistry/session');
        $this->renderLayout();
    }

    /**
     * Share giftregistry
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */
    public function sendAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }

        $giftregistry = $this->_getGiftregistry();
        if (!$giftregistry) {
            return $this->norouteAction();
        }

        $emails  = explode(',', $this->getRequest()->getPost('emails'));
        $message = nl2br(htmlspecialchars((string) $this->getRequest()->getPost('message')));
        $error   = false;
        if (empty($emails)) {
            $error = $this->__('Email address can\'t be empty.');
        }
        else {
            foreach ($emails as $index => $email) {
                $email = trim($email);
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    $error = $this->__('Please input a valid email address.');
                    break;
                }
                $emails[$index] = $email;
            }
        }
        if ($error) {
            Mage::getSingleton('giftregistry/session')->addError($error);
            Mage::getSingleton('giftregistry/session')->setSharingForm($this->getRequest()->getPost());
            $this->_redirect('*/*/share');
            return;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        try {
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            /*if share rss added rss feed to email template*/
            if ($this->getRequest()->getParam('rss_url')) {
                $rss_url = $this->getLayout()
                    ->createBlock('giftregistry/share_email_rss')
                    ->setGiftregistryId($giftregistry->getId())
                    ->toHtml();
                $message .= $rss_url;
            }
            $giftregistryBlock = $this->getLayout()->createBlock('giftregistry/share_email_items')->toHtml();

            $emails = array_unique($emails);
            /* @var $emailModel Mage_Core_Model_Email_Template */
            $emailModel = Mage::getModel('core/email_template');

            $sharingCode = $giftregistry->getSharingCode();
            foreach ($emails as $email) {
                $emailModel->sendTransactional(
                    Mage::getStoreConfig('giftregistry/email/email_template'),
                    Mage::getStoreConfig('giftregistry/email/email_identity'),
                    $email,
                    null,
                    array(
                        'customer'       => $customer,
                        'salable'        => $giftregistry->isSalable() ? 'yes' : '',
                        'items'          => $giftregistryBlock,
                        'addAllLink'     => Mage::getUrl('*/shared/allcart', array('code' => $sharingCode)),
                        'viewOnSiteLink' => Mage::getUrl('*/shared/index', array('code' => $sharingCode)),
                        'message'        => $message
                    )
                );
            }

            $giftregistry->setShared(1);
            $giftregistry->save();

            $translate->setTranslateInline(true);

            Mage::dispatchEvent('giftregistry_share', array('giftregistry' => $giftregistry));
            Mage::getSingleton('customer/session')->addSuccess(
                $this->__('Your Giftregistry has been shared.')
            );
            $this->_redirect('*/*', array('giftregistry_id' => $giftregistry->getId()));
        }
        catch (Exception $e) {
            $translate->setTranslateInline(true);

            Mage::getSingleton('giftregistry/session')->addError($e->getMessage());
            Mage::getSingleton('giftregistry/session')->setSharingForm($this->getRequest()->getPost());
            $this->_redirect('*/*/share');
        }
    }

    /**
     * Custom options download action
     * @return void
     */
    public function downloadCustomOptionAction()
    {
        $option = Mage::getModel('giftregistry/item_option')->load($this->getRequest()->getParam('id'));

        if (!$option->getId()) {
            return $this->_forward('noRoute');
        }

        $optionId = null;
        if (strpos($option->getCode(), Mage_Catalog_Model_Product_Type_Abstract::OPTION_PREFIX) === 0) {
            $optionId = str_replace(Mage_Catalog_Model_Product_Type_Abstract::OPTION_PREFIX, '', $option->getCode());
            if ((int)$optionId != $optionId) {
                return $this->_forward('noRoute');
            }
        }
        $productOption = Mage::getModel('catalog/product_option')->load($optionId);

        if (!$productOption
            || !$productOption->getId()
            || $productOption->getProductId() != $option->getProductId()
            || $productOption->getType() != 'file'
        ) {
            return $this->_forward('noRoute');
        }

        try {
            $info      = unserialize($option->getValue());
            $filePath  = Mage::getBaseDir() . $info['quote_path'];
            $secretKey = $this->getRequest()->getParam('key');

            if ($secretKey == $info['secret_key']) {
                $this->_prepareDownloadResponse($info['title'], array(
                    'value' => $filePath,
                    'type'  => 'filename'
                ));
            }

        } catch (Exception $e) {
            $this->_forward('noRoute');
        }
        exit(0);
    }
    
    public function newCustomTypeAction($data) {
        
        $session = Mage::getSingleton('customer/session');
        $newRegistryType = Mage::getModel('giftregistry/type');
        $newRegistryType->setRegistryTypeInfo($data);
        $registryTypeValidateArray = $newRegistryType->validate(); 

        if(empty($registryTypeValidateArray)) {
            $newRegistryType->save();
        } else {
            foreach ($registryTypeValidateArray as $errorMessage) {
                $session->addError($errorMessage);
            }
        }
        return $registryTypeValidateArray;
    }
    
    public function updatePostAction() {

        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }

        $data = $this->getRequest()->getPost();

        if ($this->getRequest()->isPost() && !empty($data)) {
            
            $session = Mage::getSingleton('customer/session');
            $customer = $session->getCustomer();
            
            if($data['custom_event_type']) {
                $registryTypeValidateArray = $this->newCustomTypeAction($data);
                if(empty($registryTypeValidateArray)) {
                    $typeCollection = Mage::getResourceModel('giftregistry/type_collection');
                    $data['type_id'] = $typeCollection->getLastItem()->getId();                     
                }
            } else {
                $registryTypeValidateArray = [];
            }
            
            $giftRegistry = Mage::getModel('giftregistry/giftregistry');
            $giftRegistryId = $this->getRequest()->getParam('id');
            if ($giftRegistryId) {
                $existsGiftRegistry = $giftRegistry->load($giftRegistryId);
                if ($existsGiftRegistry->getId() && $existsGiftRegistry->getCustomerId() == $customer->getId()) {
                    $giftRegistry->setId($existsGiftRegistry->getId());
                    $successMessage = Mage::helper('giftregistry')->__('Your Gift Registry Details Were Successfully Updated');
                }
                
            } else {
                $successMessage = Mage::helper('giftregistry')->__('Your Gift Registry Details Was Successfully Created');
            }           
            
            $giftRegistry = $giftRegistry->setRegistryInfo($customer, $data);
            $registryValidateArray = $giftRegistry->validate();            
            
            $validateArray = array_merge ( $registryTypeValidateArray, $registryValidateArray ); 

            if (empty($validateArray)) {
                try {
                    $giftRegistry->save();
                    
                    $session->addSuccess($successMessage);
 
                    if (Mage::getSingleton('customer/session')->getAddToGiftregistryRequest()) {
                        return $this->_addItemToGiftregistry();
                    }

                } catch (Mage_Core_Exception $e) {
                    $session->addError($e->getMessage());
                    $this->_redirect('*/*/new');
                }
            } else {
                Mage::getSingleton('core/session')->setGiftRegistryFormData($data);
                foreach ($validateArray as $errorMessage) {
                    $session->addError($errorMessage);
                }
                return $this->_redirect('*/*/new');
            }
        } else {
            throw new Exception("Insufficient Data provided");
        }
       
        $session->unsGiftRegistryFormData();
        return $this->_redirect('*/*/');
        
    }    
    
    
}
