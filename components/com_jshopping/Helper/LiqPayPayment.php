<?php
/**
 * Liqpay Payment Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category        LiqPay
 * @package         liqpay/liqpay
 * @version         3.0
 * @author          Liqpay
 * @copyright       Copyright (c) 2014 Liqpay
 * @license         http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * EXTENSION INFORMATION
 *
 * LIQPAY API       https://www.liqpay.ua/documentation/en
 *
 */

namespace Joomla\Component\Jshopping\Site\Helper;

/**
 * Payment method liqpay process
 *
 * @author      Liqpay <support@liqpay.ua>
 * @since       3.9.0
 */
final class LiqPayPayment
{
	/**
	 * @var string
	 * @since       3.9.0
	 */
	public const CURRENCY_EUR = 'EUR';

	/**
	 * @var string
	 * @since       3.9.0
	 */
	public const CURRENCY_USD = 'USD';

	/**
	 * @var string
	 * @since       3.9.0
	 */
	public const CURRENCY_UAH = 'UAH';


	/**
	 * @var int
	 * @since       3.9.0
	 */
	public const VERSION = 3;

	/**
	 * @var string
	 * @since       3.9.0
	 */
	private $_api_url = 'https://www.liqpay.ua/api/';

	/**
	 * @var string
	 * @since       3.9.0
	 */
	private $_checkout_url = 'https://www.liqpay.ua/api/3/checkout';

	/**
	 * @var string[]
	 * @since       3.9.0
	 */
	private $_supportedCurrencies = array(
		self::CURRENCY_EUR,
		self::CURRENCY_USD,
		self::CURRENCY_UAH,
	);

	/**
	 * @var string
	 * @since       3.9.0
	 */
	private $_public_key;

	/**
	 * @var string
	 * @since       3.9.0
	 */
	private $_private_key;

	/**
	 * @var null
	 * @since       3.9.0
	 */
	private $_server_response_code = null;

	/**
	 * Constructor.
	 *
	 * @param   string       $public_key
	 * @param   string       $private_key
	 * @param   string|null  $api_url  (optional)
	 *
	 * @since       3.9.0
	 */
	public function __construct(string $public_key, string $private_key, string $api_url = null)
	{
		if (empty($public_key))
		{
			throw new InvalidArgumentException('public_key is empty');
		}

		if (empty($private_key))
		{
			throw new InvalidArgumentException('private_key is empty');
		}

		$this->_public_key  = $public_key;
		$this->_private_key = $private_key;

		if (null !== $api_url)
		{
			$this->_api_url = $api_url;
		}
	}

	/**
	 * @param   string  $path
	 * @param   array   $params
	 * @param   int     $timeout
	 *
	 * @return mixed
	 *
	 * @since       3.9.0
	 */
	public function api(string $path, array $params = [], int $timeout = 5)
	{
		if (!isset($params['version']))
		{
			throw new InvalidArgumentException('version is null');
		}
		$url         = $this->_api_url . $path;
		$public_key  = $this->_public_key;
		$private_key = $this->_private_key;
		$data        = $this->encode_params(array_merge(compact('public_key'), $params));
		$signature   = $this->str_to_sign($private_key . $data . $private_key);
		$post_fields = http_build_query(array(
			'data'      => $data,
			'signature' => $signature
		));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Avoid MITM vulnerability http://phpsecurity.readthedocs.io/en/latest/Input-Validation.html#validation-of-input-sources
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    // Check the existence of a common name and also verify that it matches the hostname provided
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);   // The number of seconds to wait while trying to connect
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);          // The maximum number of seconds to allow cURL functions to execute
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output               = curl_exec($ch);
		$this->_server_response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return json_decode($server_output, false);
	}

	/**
	 * Return last api response http code
	 *
	 * @return string|null
	 * @since       3.9.0
	 */
	public function get_response_code()
	{
		return $this->_server_response_code;
	}

	/**
	 * cnb_form
	 *
	 * @param   array  $params
	 *
	 * @return string
	 *
	 * @throws InvalidArgumentException
	 * @since       3.9.0
	 */
	public function cnb_form($params)
	{
		$id       = $params['id'];
		$language = $params['language'];

		$params    = $this->cnb_params($params);
		$data      = $this->encode_params($params);
		$signature = $this->cnb_signature($params);

		return sprintf('
            <form method="POST" id="%s" action="%s" accept-charset="utf-8">
                %s
                %s
                <input type="image" src="/media/pm_liqpay/images/buttons/p1%s.radius.png" name="btn_text" />
            </form>
            ',
			$id,
			$this->_checkout_url,
			sprintf('<input type="hidden" name="%s" value="%s" />', 'data', $data),
			sprintf('<input type="hidden" name="%s" value="%s" />', 'signature', $signature),
			$language
		);
	}

	/**
	 * cnb_form raw data for custom form
	 *
	 * @param $params
	 *
	 * @return array
	 * @since       3.9.0
	 */
	public function cnb_form_raw($params)
	{
		$params = $this->cnb_params($params);

		return array(
			'url'       => $this->_checkout_url,
			'data'      => $this->encode_params($params),
			'signature' => $this->cnb_signature($params)
		);
	}

	/**
	 * cnb_signature
	 *
	 * @param   array  $params
	 *
	 * @return string
	 * @since       3.9.0
	 */
	public function cnb_signature($params)
	{
		$params      = $this->cnb_params($params);
		$private_key = $this->_private_key;

		$json = $this->encode_params($params);

		return $this->str_to_sign($private_key . $json . $private_key);
	}

	/**
	 * cnb_params
	 *
	 * @param   array  $params
	 *
	 * @return array $params
	 * @since       3.9.0
	 */
	private function cnb_params(array $params): array
	{
		$params['public_key'] = $this->_public_key;

		if (!isset($params['version']))
		{
			throw new InvalidArgumentException('version is null');
		}
		if (!isset($params['amount']))
		{
			throw new InvalidArgumentException('amount is null');
		}
		if (!isset($params['currency']))
		{
			throw new InvalidArgumentException('currency is null');
		}
		if (!in_array($params['currency'], $this->_supportedCurrencies, true))
		{
			throw new InvalidArgumentException('currency is not supported');
		}
		if (!isset($params['description']))
		{
			throw new InvalidArgumentException('description is null');
		}
		if (!isset($params['id']))
		{
			throw new InvalidArgumentException('id is null');
		}
		if (!isset($params['language']))
		{
			throw new InvalidArgumentException('language is null');
		}

		return $params;
	}

	/**
	 * encode_params
	 *
	 * @param   array  $params
	 *
	 * @return string
	 * @since       3.9.0
	 */
	private function encode_params(array $params)
	{
		return base64_encode(json_encode($params));
	}

	/**
	 * decode_params
	 *
	 * @param   string  $params
	 *
	 * @return array
	 * @since       3.9.0
	 */
	public function decode_params(string $params)
	{
		return json_decode(base64_decode($params), true);
	}

	/**
	 * str_to_sign
	 *
	 * @param   string  $str
	 *
	 * @return string
	 * @since       3.9.0
	 */
	public function str_to_sign(string $str)
	{
		return base64_encode(sha1($str, 1));
	}
}
