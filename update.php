<?php
$db = JFactory::getDbo();

$key_control_feature_exists = array_key_exists('usekey', $db->getTableColumns($db->getPrefix() . 'jshopping_addons'));
$addon = \JSFactory::getTable('addon');
$addon->loadAlias('addon_pm_liqpay');
$addon->set('name', 'Liqpay');
$addon->set('version', '1.0.0');
$addon->set('uninstall', '/administrator/components/com_jshopping/addons/addon_pm_liqpay/uninstall.php');
if ($key_control_feature_exists)  {
    $addon->set('usekey', 0);
}
$addon->store();
$db->setQuery("DELETE FROM `#__jshopping_payment_method` WHERE `payment_class`='pm_liqpay'");
$db->execute();
$config = '{"public_key":"","private_key":"","currency":"UAH","rate":"","transaction_pending_status":"1","transaction_failed_status":"3","transaction_complete_status":"6","check_data_return":"0"}';
$db->setQuery("INSERT INTO `#__jshopping_payment_method`(`payment_code`,`payment_class`,`payment_publish`,`payment_type`,`image`,`payment_params`)VALUES('liqpay','pm_liqpay',0, 2,'media/pm_liqpay/images/icon.png', '" . $config . "')");
$db->execute();
$payment_id = $db->insertid();
$db->setQuery('SELECT language FROM `#__jshopping_languages`');
foreach ($db->loadObjectList() as $language) {
    $db->setQuery("UPDATE #__jshopping_payment_method SET `name_" . $language->language . "`='Liqpay' WHERE payment_class='pm_liqpay'");
    $db->execute();
}
$back = JRoute::_('index.php?option=com_jshopping&controller=payments&task=edit&payment_id=' . $payment_id, false);