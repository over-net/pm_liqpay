<?php
defined('_JEXEC') or die('Restricted access');

$db = \JFactory::getDbo();
$db->setQuery("DELETE FROM `#__jshopping_payment_method` WHERE `payment_class`='pm_liqpay'");
$db->execute();
jimport('joomla.filesystem.folder');

$folders = [
	'media/pm_liqpay',
	'components/com_jshopping/payments/pm_liqpay',
	'administrator/components/com_jshopping/addons/addon_pm_liqpay'
];
foreach ($folders as $folder)
{
	\JFolder::delete(JPATH_ROOT . '/' . $folder);
}
jimport('joomla.filesystem.file');
$files = [
	'administrator/language/en-GB/en-GB.com_jshopping.addon_pm_liqpay.ini',
	'administrator/language/ru-RU/ru-RU.com_jshopping.addon_pm_liqpay.ini',
	'administrator/language/uk-UA/uk-UA.com_jshopping.addon_pm_liqpay.ini',
	'language/en-GB/en-GB.com_jshopping.addon_pm_liqpay.ini',
	'language/ru-RU/ru-RU.com_jshopping.addon_pm_liqpay.ini',
	'language/uk-UA/uk-UA.com_jshopping.addon_pm_liqpay.ini',
	'components/com_jshopping/Controller/LiqpayController.php',
	'components/com_jshopping/Controller/Traits/LiqPayControllerTrait.php',
];
foreach ($files as $file)
{
	\JFile::delete(JPATH_ROOT . '/' . $file);
}