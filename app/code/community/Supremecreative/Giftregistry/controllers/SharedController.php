<?php

/**
 * Giftregistry shared items controllers
 *
 * @category    Supremecreative
 * @package     Supremecreative_Giftregistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Supremecreative_Giftregistry_SharedController extends Supremecreative_Giftregistry_Controller_Abstract
{
    /**
     * Is need check a Formkey
     * @var bool
     */
    protected $_isCheckFormKey = false;

    /**
     * Retrieve giftregistry instance by requested code
     *
     * @return Supremecreative_Giftregistry_Model_Giftregistry|false
     */
    protected function _getGiftregistry()
    {
        $code     = (string)$this->getRequest()->getParam('code');
        if (empty($code)) {
            return false;
        }

        $giftregistry = Mage::getModel('giftregistry/giftregistry')->loadByCode($code);
        if (!$giftregistry->getId()) {
            return false;
        }

        Mage::getSingleton('checkout/session')->setSharedGiftregistry($code);

        return $giftregistry;
    }

    /**
     * Shared giftregistry view page
     *
     */
    public function indexAction()
    {
        $giftregistry   = $this->_getGiftregistry();
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();

        if ($giftregistry && $giftregistry->getCustomerId() && $giftregistry->getCustomerId() == $customerId) {
            $this->_redirectUrl(Mage::helper('giftregistry')->getListUrl($giftregistry->getId()));
            return;
        }

        Mage::register('shared_giftregistry', $giftregistry);

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('giftregistry/session');
        $this->renderLayout();
    }

    /**
     * Add shared giftregistry item to shopping cart
     *
     * If Product has required options - redirect
     * to product view page with message about needed defined required options
     *
     */
    public function cartAction()
    {
        $itemId = (int) $this->getRequest()->getParam('item');
        $code = $this->getRequest()->getParam('code');

        /* @var $item Supremecreative_Giftregistry_Model_Item */
        $item = Mage::getModel('giftregistry/item')->load($itemId);
        $giftregistry = Mage::getModel('giftregistry/giftregistry')->loadByCode($code);
        $redirectUrl = Mage::getUrl('*/*/index', array('code' => $code));

        /* @var $session Supremecreative_Giftregistry_Model_Session */
        $session    = Mage::getSingleton('giftregistry/session');
        $cart       = Mage::getSingleton('checkout/cart');

        try {
            $options = Mage::getModel('giftregistry/item_option')->getCollection()
                    ->addItemFilter(array($itemId));
            $item->setOptions($options->getOptionsByItem($itemId));

            $item->addToCart($cart);
            $cart->save()->getQuote()->collectTotals();

            if (Mage::helper('checkout/cart')->getShouldRedirectToCart()) {
                $redirectUrl = Mage::helper('checkout/cart')->getCartUrl();
            }
            $session->setSharedGiftRegistry($giftregistry);
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Supremecreative_Giftregistry_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                $session->addError(Mage::helper('giftregistry')->__('This product(s) is currently out of stock'));
            } elseif ($e->getCode() == Supremecreative_Giftregistry_Model_Item::EXCEPTION_CODE_NOT_SPECIFIED_PRODUCT) {
                if (!$giftregistry->getItemsCount()) {
                    $redirectUrl = Mage::helper('checkout/cart')->getCartUrl();
                    $session = Mage::getSingleton('catalog/session');
                }
                $message = Mage::helper('giftregistry')->__('Cannot add the selected product to shopping cart because the product was removed from the giftregistry');
                $session->addNotice($message);
            } else {
                Mage::getSingleton('catalog/session')->addNotice($e->getMessage());
                $redirectUrl = $item->getProductUrl();
            }
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('giftregistry')->__('Cannot add item to shopping cart'));
        }

        return $this->_redirectUrl($redirectUrl);
    }
}
