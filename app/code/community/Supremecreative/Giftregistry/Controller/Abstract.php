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
 * Giftregistry Abstract Front Controller Action
 *
 * @category    Supremecreative
 * @package     Supremecreative_Giftregistry
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Supremecreative_Giftregistry_Controller_Abstract extends Mage_Core_Controller_Front_Action
{
    /**
     * Filter to convert localized values to internal ones
     * @var Zend_Filter_LocalizedToNormalized
     */
    protected $_localFilter = null;

    /**
     * Is need check a Formkey
     * @var bool
     */
    protected $_isCheckFormKey = true;

    /**
     * Processes localized qty (entered by user at frontend) into internal php format
     *
     * @param string $qty
     * @return float|int|null
     */
    protected function _processLocalizedQty($qty)
    {
        if (!$this->_localFilter) {
            $this->_localFilter = new Zend_Filter_LocalizedToNormalized(
                array('locale' => Mage::app()->getLocale()->getLocaleCode())
            );
        }
        $qty = $this->_localFilter->filter((float)$qty);
        if ($qty < 0) {
            $qty = null;
        }
        return $qty;
    }

    /**
     * Retrieve current giftregistry instance
     *
     * @return Supremecreative_Giftregistry_Model_Giftregistry|false
     */
    abstract protected function _getGiftregistry();

    /**
     * Add all items from giftregistry to shopping cart
     *
     */
    public function allcartAction()
    {
        if ($this->_isCheckFormKey && !$this->_validateFormKey()) {
            $this->_forward('noRoute');
            return;
        }

        $giftregistry   = $this->_getGiftregistry();
        if (!$giftregistry) {
            $this->_forward('noRoute');
            return;
        }
        $isOwner    = $giftregistry->isOwner(Mage::getSingleton('customer/session')->getCustomerId());

        $messages   = array();
        $addedItems = array();
        $notSalable = array();
        $hasOptions = array();

        $cart       = Mage::getSingleton('checkout/cart');
        $collection = $giftregistry->getItemCollection()
                ->setVisibilityFilter();

        $qtysString = $this->getRequest()->getParam('qty');
        if (isset($qtysString)) {
            $qtys = array_filter(json_decode($qtysString), 'strlen');
        }

        foreach ($collection as $item) {
            /** @var Supremecreative_Giftregistry_Model_Item */
            try {
                $disableAddToCart = $item->getProduct()->getDisableAddToCart();
                $item->unsProduct();

                // Set qty
                if (isset($qtys[$item->getId()])) {
                    $qty = $this->_processLocalizedQty($qtys[$item->getId()]);
                    if ($qty) {
                        $item->setQty($qty);
                    }
                }
                $item->getProduct()->setDisableAddToCart($disableAddToCart);
                // Add to cart
                if ($item->addToCart($cart, $isOwner)) {
                    $addedItems[] = $item->getProduct();
                }

            } catch (Mage_Core_Exception $e) {
                if ($e->getCode() == Supremecreative_Giftregistry_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                    $notSalable[] = $item;
                } else if ($e->getCode() == Supremecreative_Giftregistry_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                    $hasOptions[] = $item;
                } else {
                    $messages[] = $this->__('%s for "%s".', trim($e->getMessage(), '.'), $item->getProduct()->getName());
                }

                $cartItem = $cart->getQuote()->getItemByProduct($item->getProduct());
                if ($cartItem) {
                    $cart->getQuote()->deleteItem($cartItem);
                }
            } catch (Exception $e) {
                Mage::logException($e);
                $messages[] = Mage::helper('giftregistry')->__('Cannot add the item to shopping cart.');
            }
        }

        if ($isOwner) {
            $indexUrl = Mage::helper('giftregistry')->getListUrl($giftregistry->getId());
        } else {
            $indexUrl = Mage::getUrl('giftregistry/shared', array('code' => $giftregistry->getSharingCode()));
        }
        if (Mage::helper('checkout/cart')->getShouldRedirectToCart()) {
            $redirectUrl = Mage::helper('checkout/cart')->getCartUrl();
        } else if ($this->_getRefererUrl()) {
            $redirectUrl = $this->_getRefererUrl();
        } else {
            $redirectUrl = $indexUrl;
        }

        if ($notSalable) {
            $products = array();
            foreach ($notSalable as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $messages[] = Mage::helper('giftregistry')->__('Unable to add the following product(s) to shopping cart: %s.', join(', ', $products));
        }

        if ($hasOptions) {
            $products = array();
            foreach ($hasOptions as $item) {
                $products[] = '"' . $item->getProduct()->getName() . '"';
            }
            $messages[] = Mage::helper('giftregistry')->__('Product(s) %s have required options. Each of them can be added to cart separately only.', join(', ', $products));
        }

        if ($messages) {
            $isMessageSole = (count($messages) == 1);
            if ($isMessageSole && count($hasOptions) == 1) {
                $item = $hasOptions[0];
                if ($isOwner) {
                    $item->delete();
                }
                $redirectUrl = $item->getProductUrl();
            } else {
                $giftregistrySession = Mage::getSingleton('giftregistry/session');
                foreach ($messages as $message) {
                    $giftregistrySession->addError($message);
                }
                $redirectUrl = $indexUrl;
            }
        }

        if ($addedItems) {
            // save giftregistry model for setting date of last update
            try {
                $giftregistry->save();
            }
            catch (Exception $e) {
                Mage::getSingleton('giftregistry/session')->addError($this->__('Cannot update giftregistry'));
                $redirectUrl = $indexUrl;
            }

            $products = array();
            foreach ($addedItems as $product) {
                $products[] = '"' . $product->getName() . '"';
            }

            Mage::getSingleton('checkout/session')->addSuccess(
                Mage::helper('giftregistry')->__('%d product(s) have been added to shopping cart: %s.', count($addedItems), join(', ', $products))
            );

            // save cart and collect totals
            $cart->save()->getQuote()->collectTotals();
        }

        Mage::helper('giftregistry')->calculate();

        $this->_redirectUrl($redirectUrl);
    }
}
