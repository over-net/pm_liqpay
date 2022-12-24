<?php
/**
 * @package         Joomla\Component\Jshopping\Site\Controller
 * @subpackage      pm_payment
 *
 * @author          M.Kulyk
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @since
 */

namespace Joomla\Component\Jshopping\Site\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Jshopping\Site\Helper\LiqPayPayment;
use Joomla\Component\Jshopping\Site\Controller\Traits\LiqPayControllerTrait;
use Joomla\Input\Input;


/**
 * @package     Joomla\Component\Jshopping\Site\Controller
 *
 * @since       3.10.0
 */
class LiqpayController extends \Joomla\CMS\MVC\Controller\BaseController
{

	/**
	 * The factory.
	 *
	 * @var    MVCFactoryInterface
	 * @since  4.1
	 */
	protected $factory;

	/**
	 * The Application
	 *
	 * @var    CMSApplication|null
	 * @since  4.1
	 */
	protected $app;

	/**
	 * Hold a JInput object for easier access to the input variables.
	 *
	 * @var    Input
	 * @since  4.1
	 */
	protected $input;


	use LiqPayControllerTrait;


	/**
	 * @param   array                                             $config
	 * @param   \Joomla\CMS\MVC\Factory\MVCFactoryInterface|null  $factory
	 * @param   \Joomla\CMS\Application\CMSApplication|null       $app
	 * @param   \Joomla\Input\Input|null                          $input
	 *
	 * @since  4.1
	 */
	public function __construct(array $config = [], MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		if (!$this->isValidControlKey())
		{
			header('HTTP/1.0 403 Forbidden');
			die();
		}
	}

	/**
	 *
	 * @return int
	 *
	 * @since 4.1
	 */
	final public function revision(): int
	{
		try
		{
			$db    = self::getDBO();
			$query = $db->getQuery(true);

			$query->select($db->quoteName(['order_id']));
			$query->from($db->quoteName('#__jshopping_orders'));
			$query->where($db->quoteName('payment_method_id') . ' = ' . $db->quote($this->paymentMethod()->payment_id));
			$query->andWhere($db->quoteName('order_status') . ' = ' . $db->quote('1'));
			$query->andWhere($db->quoteName('order_created') . ' = ' . $db->quote('1'));
			$db->setQuery($query);
			$results = $db->loadAssocList('order_id');


			$order_ids = array_keys($results);

			$liqpay = new LiqPayPayment($this->paymentParams()->public_key, $this->paymentParams()->private_key);

			foreach ($order_ids as $order_id)
			{
				$payment = $liqpay->api("request", [
					'action'   => 'status',
					'version'  => LiqPayPayment::VERSION,
					'order_id' => $order_id
				]);

				if ($payment->status === 'success')
				{
					$query = "update #__jshopping_orders set 
                        order_status='" . $db->escape('6') . "', order_created='" . $db->escape('1') . "' 
                        WHERE order_id = '" . $db->escape($order_id) . "'";
					$db->setQuery($query);
					$db->execute();
				}

			}
			if (count($order_ids))
			{
				return Status::OK;
			}

			return Status::NO_TASK;

		}
		catch (\Exception $exception)
		{
			return Status::KNOCKOUT;
		}

	}


}