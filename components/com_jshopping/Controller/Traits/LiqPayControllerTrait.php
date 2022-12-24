<?php
/**
 * @package         \Joomla\Component\Jshopping\Site\Controller\Traits
 * @subpackage      pm_payment
 *
 * @author          M.Kulyk
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @since
 */


namespace Joomla\Component\Jshopping\Site\Controller\Traits;


use Joomla\Database\DatabaseDriver;


trait LiqPayControllerTrait
{

	/**
	 *
	 * @return bool
	 *
	 * @since 4.1
	 */
	private function isValidControlKey(): bool
	{
		return $this->input->getString('key') === $this->paymentParams()->public_key;
	}

	/**
	 *
	 * @return \Joomla\Database\DatabaseDriver
	 *
	 * @since 3.10.0
	 */
	private static function getDBO(): DatabaseDriver
	{
		return \JFactory::getDBO();
	}


	/**
	 *
	 * @return object
	 *
	 * @since 4.1
	 */
	private function paymentMethod(): object
	{
		$pm_method = \JSFactory::getTable('paymentMethod');
		$pm_method->loadFromClass('pm_liqpay');

		return $pm_method;
	}

	/**
	 *
	 * @return \stdClass
	 *
	 * @since 4.1
	 */
	private function paymentParams(): \stdClass
	{
		return json_decode($this->paymentMethod()->payment_params, false);
	}


}