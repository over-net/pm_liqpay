<?php
/**
 * @package         pm_payment
 *
 * @author          M.Kulyk
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @since
 */
defined('_JEXEC') or die();

use Joomla\CMS\Application\WebApplication;
use Joomla\Component\Jshopping\Site\Helper\LiqPayPayment;

/**
 * @package     pm_liqpay
 *
 * @since       4.1.0
 */
final class pm_liqpay extends PaymentRoot
{
    /**
     * @var WebApplication|null
     * @since 4.1.0
     */
    private static $app;


    /**
     * @throws \Exception
     * @since 4.1.0
     */
    public function __construct()
    {
        if (self::$app === null) {
            self::$app = JFactory::getApplication();
        }
        self::$app->getLanguage()->load("com_jshopping.addon_pm_liqpay");
    }


    /**
     * @param $params
     * @param $pmconfigs
     *
     *
     * @since 4.1.0
     */
    public function showPaymentForm($params, $pmconfigs)
    {
        $doc = self::$app->getDocument();
        $style = "
		[value=pm_liqpay]+label * { height: 48px; vertical-align: middle; display:inline-block; }
		[value=pm_liqpay]+label .payment_image{ margin-right: 5px; display: inline-block; }
	";
        $doc->addStyleDeclaration($style);

    }


    /**
     * @param $pmconfigs
     *
     *
     * @since 4.1.0
     */
    public function showAdminFormParams($pmconfigs)
    {
        include 'adminparamsform.php';
    }


    /**
     * @param $pmconfigs
     * @param $order
     *
     *
     * @since 4.1
     */
    public function showEndForm($pmconfigs, $order)
    {
        $jshopConfig = \JSFactory::getConfig();
	$itemId = $pmconfigs['return_item_id'] ?? null;
	$lang = self::language();
	$doc = self::$app->getDocument();

        echo JText::_('JSHOPPING_ADDON_PM_LIQPAY_REDIRECTING_MESSAGE');
	    

        echo $this->liqpay($pmconfigs['public_key'], $pmconfigs['private_key'])->cnb_form([
            'id' => 'liqpay_form',
            'action' => 'pay',
            'language' => self::language(),
            'amount' => $order->order_total,
            'currency' => $pmconfigs['currency'],
            'description' => JText::sprintf('JSHOPPING_ADDON_PM_LIQPAY_ORDER_DETAILS', $order->order_number, $jshopConfig->shop_name),
            'order_id' => $order->order_id,
            'result_url' => JUri::root() . "index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_liqpay&Itemid={$itemId}&lang={$lang}&order_id={$order->order_id}",
            'server_url' => JUri::root() . "index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_liqpay&no_lang=1&order_id={$order->order_id}",
            'version' => LiqPayPayment::VERSION
        ]);
	    
	$script = "
	jQuery( document ).ready(function() {
		setTimeout(
			jQuery('#liqpay_form').submit(), 
		1000);
	});
	";

	$doc->addScriptDeclaration($script);
	    
    }


    /**
     * @param $pmconfigs
     *
     * @return array
     *
     * @since 4.1
     */
    public function getUrlParams($pmconfigs)
    {
        return [
            'order_id' => self::$app->input->getString("order_id", "", "POST"),
            'hash' => "",
            'checkHash' => 0,
            'checkReturnParams' => $pmconfigs['check_data_return']
        ];
    }

    /**
     * @param $modelCheckout
     *
     *
     * @since 4.1
     */
    public function noCheckReturnExecute($modelCheckout)
    {

        $order_id = $modelCheckout->getOrderId();
        $pmconfigs = $modelCheckout->getPmConfigs();
        $order = $this->getOrderByID($order_id);
        $payment = $this->liqpay($pmconfigs['public_key'], $pmconfigs['private_key'])->api("request", [
            'action' => 'status',
            'version' => LiqPayPayment::VERSION,
            'order_id' => $order_id
        ]);


        if (isset($payment->code) && $payment->code === 'payment_not_found') {

            \JSError::raiseWarning("", \JText::_('JSHOPPING_ADDON_PM_LIQPAY_PAYMENT_ERROR'));
        }
        if (isset($payment->order_id, $payment->result) && $payment->result === 'ok' && (int)$payment->order_id === (int)$order_id) {
            if ($order_id) {
                $this->touchOrder((int)$order_id, 6);
            }
            \JSError::raiseNotice("", \JText::sprintf('JSHOPPING_ADDON_PM_LIQPAY_PAYMENT_SUCCESS', $order->order_number));

        }
    }

    /**
     * @param $pmconfigs
     * @param $order
     * @param $act
     *
     * @return array|void
     *
     * @since 4.1
     */
    public function checkTransaction($pmconfigs, $order, $act)
    {
        $payment = $this->liqpay($pmconfigs['public_key'], $pmconfigs['private_key'])->api("request", [
            'action' => 'status',
            'version' => LiqPayPayment::VERSION,
            'order_id' => $order->order_id
        ]);

        JSHelper::saveToLog("liqpay.log", "> " . serialize($payment));

        if ($payment->status === 'error') {
            return [3, ''];
        }

        switch ($payment->status) {
            case "try_again":
            case "success":
                JSHelper::saveToLog("liqpay.log", "> " . $payment->order_id . ":COMPLETED");

                return [9, ''];

            case "failure":
                JSHelper::saveToLog("liqpay.log", "> " . $payment->order_id . ":FAILED");

                return [3, ''];

            case "wait_sender":
                JSHelper::saveToLog("liqpay.log", "> " . $payment->order_id . ":WAITING");

                return [2, ''];
        }
    }


    /**
     * @param int $order_id
     * @param int $order_status
     *
     * @since 4.1
     */
    private function touchOrder($order_id, $order_status)
    {
        $db = \JFactory::getDBO();
        $query = "update #__jshopping_orders set order_status='" . $db->escape($order_status) . "' WHERE order_id = '" . $db->escape($order_id) . "'";
        $db->setQuery($query);
        $db->execute();
    }


    /**
     * @param $order_id
     *
     * @return object
     *
     * @since 4.1
     */
    private function getOrderByID($order_id)
    {
        $order = \JSFactory::getTable('order');
        $order->load($order_id);

        return $order;
    }


    /**
     * @param string $public_key
     * @param string $private_key
     *
     * @return \Joomla\Component\Jshopping\Site\Helper\LiqPayPayment
     *
     * @since 4.1
     */
    private function liqpay(string $public_key, string $private_key): LiqPayPayment
    {
        return new LiqPayPayment($public_key, $private_key);
    }


    /**
     *
     * @return mixed|string
     *
     * @since 4.1
     */
    private static function language(): string
    {
        $lang = self::$app->getLanguage();
        $langTag = explode('-', $lang->getTag());

        return $langTag[0] ?? 'en';
    }


}
