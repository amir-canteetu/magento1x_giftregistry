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

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'giftregistry/giftregistry'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('giftregistry/giftregistry'))
    ->addColumn('giftregistry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Giftregistry ID')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Customer ID')
    ->addColumn('shared', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Sharing flag (0 or 1)')
    ->addColumn('sharing_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        ), 'Sharing encrypted code')
    ->addColumn('banner_img', Varien_Db_Ddl_Table::TYPE_TEXT, 36, array(
        ), 'Banner Image')        
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
            ),
            'Type Id')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
            array(
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
            ),
            'Website Id'
        )
        ->addColumn('owner', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            array(),
            'Reg Owners Name'
        )
        ->addColumn('event_date', Varien_Db_Ddl_Table::TYPE_DATE, null,
            array(),
            'Event Date'
        )
        ->addColumn('event_location', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            array('nullable'  => true),
            'Event Location'
        )
        ->addColumn('number_guests', Varien_Db_Ddl_Table::TYPE_BIGINT, null,
            array('nullable'  => true),
            'Number Of Guests'
        ) 
        ->addColumn('event_message', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
            'nullable'  => true,
        ), 'Registry Message')                      
    ->addColumn('shipping_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(        
        'unsigned'  => true,
        'nullable'  => false
        ), 'Shipping Address Id')         
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Last updated date')
    ->addIndex($installer->getIdxName('giftregistry/giftregistry', 'shared'), 'shared')
    ->addIndex(
        $installer->getIdxName('giftregistry/giftregistry', 'customer_id', Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        'customer_id',
        array('type'=>Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey($installer->getFkName('giftregistry/giftregistry', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Giftregistry main Table');
$installer->getConnection()->createTable($table);

/**
 * Create Registry 'giftregistry/type' Table 
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('giftregistry/type'))
    ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Type Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 250, array(
        'nullable'  => true,
    ), 'name')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Customer Id'
    )
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ),
        'Store Id')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '1',
    ), 'Is Active')
    ->setComment('Registry Type Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'giftregistry/item'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('giftregistry/item'))
    ->addColumn('giftregistry_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Giftregistry item ID')
    ->addColumn('giftregistry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Giftregistry ID')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Product ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => true,
        ), 'Store ID')
    ->addColumn('added_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Add date and time')
    ->addColumn('description', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Short description of gift registry item')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        ), 'Qty')
    ->addIndex($installer->getIdxName('giftregistry/item', 'giftregistry_id'), 'giftregistry_id')
    ->addForeignKey($installer->getFkName('giftregistry/item', 'giftregistry_id', 'giftregistry/giftregistry', 'giftregistry_id'),
        'giftregistry_id', $installer->getTable('giftregistry/giftregistry'), 'giftregistry_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('giftregistry/item', 'product_id'), 'product_id')
    ->addForeignKey($installer->getFkName('giftregistry/item', 'product_id', 'catalog/product', 'entity_id'),
        'product_id', $installer->getTable('catalog/product'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addIndex($installer->getIdxName('giftregistry/item', 'store_id'), 'store_id')
    ->addForeignKey($installer->getFkName('giftregistry/item', 'store_id', 'core/store', 'store_id'),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Giftregistry items');
$installer->getConnection()->createTable($table);

/**
 * Create table 'giftregistry/item_option'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('giftregistry/item_option'))
    ->addColumn('option_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Option Id')
    ->addColumn('giftregistry_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Giftregistry Item Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Product Id')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Code')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable'  => true,
        ), 'Value')
    ->addForeignKey(
        $installer->getFkName('giftregistry/item_option', 'giftregistry_item_id', 'giftregistry/item', 'giftregistry_item_id'),
        'giftregistry_item_id', $installer->getTable('giftregistry/item'), 'giftregistry_item_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Giftregistry Item Option Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();
