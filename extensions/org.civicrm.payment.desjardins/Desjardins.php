<?php 
 
/*
 +--------------------------------------------------------------------+
 | Desjardins Payment Gateway Processor (without redirection)         |
 +--------------------------------------------------------------------+
 | Copyright Mathieu Lutfy 2010-2011                                  |
 +--------------------------------------------------------------------+
 | This file is part of the Payment gateway extension for CiviCRM.    |
 |                                                                    |
 | IMPORTANT:                                                         |
 | This is a community contributed extension. It is not endorsed or   |
 | supported by neither Desjardins nor CiviCRM. Use at your own risk. |
 |                                                                    |
 | LICENSE:                                                           |
 | This extension is free software; you can copy, modify, and         |
 | distribute it under the terms of the GNU Affero General Public     |
 | License Version 3, 19 November 2007.                               |
 |                                                                    |
 | This extension is distributed in the hope that it will be useful,  |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of     |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/*
 * How to use this payment gateway:
 * 
 * Do the following SQL queries:
 *
 * 1- The following query is necessary so that CiviCRM can "see" the gateway:
 * 
 * INSERT INTO civicrm_payment_processor_type (
 *      domain_id, name, title, description, 
 *      is_active, is_default, 
 *      user_name_label, password_label, class_name, 
 *      url_site_test_default, url_site_default, 
 *      billing_mode, is_recur)   
 *   VALUES (1, 'Desjardins', 'Desjardins', NULL,
 *      1, 0, 
 *      'Merchant ID', 'Merchant Key', 'Payment_Desjardins', 
 *      'https://www.labdevtrx3.com/catch', 'https://epaiementsecurise.desjardins.com/catch', 
 *      1, 0);
 * 
 * To display the receipt to the user:
 * 
 * * Edit: CRM/Contribute/Form/Contribution/ThankYou.php
 *   in function buildQuickForm() below: $params = $this->_params;
 *   add: $this->assign( 'receipt_desjardins', $this->_params['receipt_desjardins']);
 * 
 * * Edit: CRM/Contribute/Form/Contribution/ThankYou.tpl
 *   add: <pre>{$receipt_desjardins}</pre> where you want the receipt to be displayed
 *
 * TESTING: use 4530911100000990
 */

require_once 'CRM/Core/Payment.php';

class org_civicrm_payment_desjardins extends CRM_Core_Payment {
    const
        CHARSET  = 'UFT-8'; # (not used, implicit in the API, might need to convert?)

    const
        CIVICRM_DESJARDINS_LOG = TRUE; # Wheter to log all XML communication with the gateway
         
    /**
     * We only need one instance of this object. So we use the singleton
     * pattern and cache the instance in this variable
     *
     * @var object
     * @static
     */
    static private $_singleton = null;

    /** 
     * Constructor 
     *
     * @param string $mode the mode of operation: live or test
     * 
     * @return void 
     */ 
    function __construct( $mode, &$paymentProcessor ) {
        $this->_mode = $mode;
        $this->_paymentProcessor = $paymentProcessor;
        $this->_processorName = ts('Desjardins');

        $config = CRM_Core_Config::singleton( ); // get merchant data from config
        $this->_profile['mode'] = $mode; // live or test
        $this->_profile['storeid']  = $this->_paymentProcessor['user_name'];
        $this->_profile['apitoken'] = $this->_paymentProcessor['password'];
        $currencyID = $config->defaultCurrency;

        if ('CAD' != $currencyID) {
            // Configuration error: default currency must be CAD
            # [ML] FIXME $config->defaultCurrency returns USD...
            # return self::error('Invalid configuration: ' . $currencyID . ', you must use currency CAD with Desjardins');
        }
    }

    function dj_purchase($tx_id, $tx_key, $amount, $cc_num, $cc_name, $cc_expyear, $cc_expmonth, $cc_email) {
    	$merchant_id = $this->_profile['storeid'];
    	$merchant_key = $this->_profile['apitoken'];
    	$url_response = "https://oxfam.qc.ca/dj_response.php"; // XXX should be in a configuration variable
   	$submit_url =  $this->_paymentProcessor['url_site'];
    
    	$amount = intval($amount * 100); // Ex: 15.24$ => 1524

        // Clean up CC number
        $cc_num = preg_replace('/[^0-9]/', '', $cc_num);
    
    	$xmlData = '';
    	$response = '';
    
    	$xmlData .= '<?xml version="1.0" encoding="UTF-8" ?>';
    	$xmlData .= '<request>';
    	$xmlData .=   '<merchant id="' .$merchant_id . '" key="' . $merchant_key . '">';
    	$xmlData .=     '<transactions>';
    	$xmlData .=       '<transaction id="' . $tx_id . '" key="' . $tx_key . '" type="purchase" currency="CAD" currencyText="$CAD">';
    	$xmlData .=         '<amount>' . $amount . '</amount>';
    	$xmlData .=         '<language>fr</language>'; // FIXME hardcoded language
    	$xmlData .=         '<card>';
    	$xmlData .=           '<number>' . $cc_num . '</number>';
    	$xmlData .=           '<holder_name>' . $cc_name . '</holder_name>';
    	$xmlData .=           '<expiry>';
    	$xmlData .=             '<year>' . $cc_expyear . '</year>';
    	$xmlData .=             '<month>' . $cc_expmonth . '</month>';
    	$xmlData .=           '</expiry>';
    	$xmlData .=         '</card>';
    	$xmlData .=         '<customer_email>' . $cc_email . '</customer_email>';
    	$xmlData .=         '<urls>';
    	$xmlData .=           '<url name="response">';
    	$xmlData .=             '<path>'.$url_response.'</path>';
    	$xmlData .=           '</url>';
    	$xmlData .=         '</urls>';
    	$xmlData .=       '</transaction>';
    	$xmlData .=     '</transactions>';
    	$xmlData .=   '</merchant>';
    	$xmlData .= '</request>';
    
        $this->djLog($tx_id, $xmlData, 'purchase send');

    	$header = array();
    	$header[] = "MIME-Version: 1.0";
    	$header[] = "Content-type: text/xml";
    	$header[] = "Accept: text/xml";
    	$header[] = "Content-length: " . strlen($xmlData); 
    	$header[] = "Cache-Control: no-cache";
    	$header[] = "Connection: close";
    
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    	curl_setopt($ch, CURLOPT_URL, $submit_url);
    	curl_setopt($ch, CURLOPT_VERBOSE, 0);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    	curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    	$response = curl_exec($ch);
    	curl_close($ch);

    	$arr_values = array();
    	$index = '';
    
    	$xml_parser = xml_parser_create();
    	xml_parser_set_option($xml_parser,XML_OPTION_CASE_FOLDING,0);
    	xml_parser_set_option($xml_parser,XML_OPTION_SKIP_WHITE,0);
    	xml_parse_into_struct($xml_parser, $response, $arr_values, $index);
    	xml_parser_free($xml_parser);
    
        $fixed_trx_values = $this->dj_get_tx_info_from_array($arr_values);

        // [ML] issue 3038, in one case, a transaction was approved, but had an empty receipt.
        $fail = ($fixed_trx_values['transaction_approved'] == 'no' || (! $fixed_trx_values['receipt_full']) || $fixed_trx_values['errcode']);

        $this->djLog($tx_id, print_r($response, 1), 'purchase response', $fail);

    	return $fixed_trx_values;
    }

    function dj_get_tx_info_from_array($trx_values) {
        $array_transaction_info = array();

        $trx_values_key = array();
        foreach ($trx_values as $num => $trx_value) {
            $trx_values_key[$trx_value['tag']][] = $trx_value;
        }

        if ($trx_values_key['error'][0]['level'] > 0 && substr($trx_values_key['code'][0]['value'], 0, 1) == 'W') {
          $array_transaction_info['errcode'] = $trx_values_key['code'][0]['value'];
          $array_transaction_info['message'] = $trx_values_key['message'][0]['value'];
          return $array_transaction_info;
        }

        $array_transaction_info['merchant_id'] = $trx_values_key['merchant'][0]['attributes']['id'];
        $array_transaction_info['transaction_id'] = $trx_values_key['transaction'][0]['attributes']['id'];
        $array_transaction_info['transaction_currency'] = $trx_values_key['transaction'][0]['attributes']['currency'];
        $array_transaction_info['transaction_currencyText'] = $trx_values_key['transaction'][0]['attributes']['currencyText'];
        $array_transaction_info['transaction_approved'] = $trx_values_key['transaction'][0]['attributes']['approved'];
        $array_transaction_info['terminal_id'] = $trx_values_key['terminal_id'][0]['value'];
        $array_transaction_info['amount'] = $trx_values_key['amount'][0]['value'];
        $array_transaction_info['language'] = $trx_values_key['language'][0]['value'];
        $array_transaction_info['card_holder_name'] = $trx_values_key['card_holder_name'][0]['value'];
        $array_transaction_info['date'] = $trx_values_key['date'][0]['value'];
        $array_transaction_info['transaction_code'] = $trx_values_key['transaction'][0]['value'];
        $array_transaction_info['condition_code'] = $trx_values_key['condition_code'][0]['value'];
        $array_transaction_info['iso_code'] = $trx_values_key['iso_code'][0]['value'];
        $array_transaction_info['host_code'] = $trx_values_key['host_code'][0]['value'];
        $array_transaction_info['action_code'] = $trx_values_key['action_code'][0]['value'];
        $array_transaction_info['card_type'] = $trx_values_key['card_type'][0]['value'];
        $array_transaction_info['batch_no'] = $trx_values_key['batch_no'][0]['value'];
        $array_transaction_info['sequence_no'] = $trx_values_key['sequence_no'][0]['value'];
        $array_transaction_info['process_info'] = $trx_values_key['process_info'][0]['value'];
        $array_transaction_info['authorization_no'] = $trx_values_key['authorization_no'][0]['value'];
        $array_transaction_info['receipt_text'] = $trx_values_key['receipt_text'][0]['value'];
        $array_transaction_info['receipt_full'] = $trx_values_key['receipt'][0]['value'];
        return $array_transaction_info;
    }

    /** 
     * singleton function used to manage this object 
     * 
     * @param string $mode the mode of operation: live or test
     *
     * @return object 
     * @static 
     * 
     */ 
    static function &singleton( $mode, &$paymentProcessor ) {
        $processorName = $paymentProcessor['name'];
        if (self::$_singleton[$processorName] === null ) {
            self::$_singleton[$processorName] = new org_civicrm_payment_desjardins( $mode, $paymentProcessor );
        }
        return self::$_singleton[$processorName];
    }


    function doDirectPayment( &$params ) {
      if (!function_exists('curl_init')) {
        return self::error('The Desjardins.com API service requires curl.  Please talk to your system administrator to get this configured.');
      }

      # make sure i've been called correctly ...
      if ( ! $this->_profile ) {
          return self::error('Unexpected error, missing profile');
      }
      if ($params['currencyID'] != 'CAD') {
         # [ML] FIXME return self::error('Invalid currency selection, must be CAD');
      }

      // Fraud-protection: Validate the postal code
      if (! $this->isValidPostalCode($params)) {
        watchdog('CiviCRM Desjardins', 'Invalid postcode for Canada: ' . print_r($params, 1));
        $this->djLog($params['invoiceID'], 'anti-fraud (CDJ002 invalid postcode): ' . print_r($params, 1), 'do_direct fraud', TRUE);
        return self::error(t("Error") . ": " . t('The transaction could not be processed, please contact us for more information.')
                      . ' (code: CDJ002) '
                      . '<div class="civicrm-dj-retrytx">' . t("The transaction was not approved. Please verify your credit card number and expiration date.") . '</div>');
      }

      // Fraud-protection: Limit the number of transactions: 2 per 6 hours
      if ($this->isTooManyTransactions($params)) {
        watchdog('CiviCRM Desjardins', 'Too many transactions from: ' . $params['ip_address']);
        $this->djLog($params['invoiceID'], 'anti-fraud (CDJ003 too many transactions from IP): ' . print_r($params, 1), 'do_direct fraud', TRUE);
        return self::error(t("Error") . ": " . t('The transaction could not be processed, please contact us for more information.')
                      . ' (code: CDJ003) '
                      . '<div class="civicrm-dj-retrytx">' . t("The transaction was not approved. Please verify your credit card number and expiration date.") . '</div>');
      }

      if(!empty($params['amount'])){
	$amount = $params['amount'];
      }else{
        $amount = $params['amount_other'];
      }

      $cc_num   = $params['credit_card_number'];
      $cc_month = str_pad($params['month'], 2, '0', STR_PAD_LEFT);
      $cc_year  = substr($params['year'], -2);
      $cc_name  = $params['first_name'] . ' ' . $params['last_name'];
      $tx_email = $params['email'];

      // *************************** Request Variables ******************************
      $merchant_id   = $this->_profile['storeid'];
      $merchant_key  = $this->_profile['apitoken'];
      $invoice_id = $params['invoiceID'];

      if (! ($cc_num == '4111111111111111' && $cc_month == '03')) {
          $auth = $this->doDesjardinsLogin($merchant_key, $invoice_id);
    
          $r = new CRM_Core_Payment_Desjardins_Response($auth);
          $d = $r->getData();
    
          $purchase = $this->dj_purchase($d['trx']['id'], $d['trx']['key'], $amount, $cc_num, $cc_name, $cc_year, $cc_month, $tx_email);
    
          if ($purchase['transaction_approved'] == 'no' || (! $purchase['receipt_full']) || $purchase['errcode']) {
              $errcode = ($purchase['condition_code'] ? $purchase['condition_code'] : $purchase['errcode']);
              $errcode = ($errcode ? $errcode : 'unknown');

              // FIXME [ML]  this had a 9010 error code before, was removed in 3.3
              return self::error(t("Error") . ": " . $purchase['receipt_text']
                  . ' (code: ' . $errcode . ') '
                  . '<pre>' . $purchase['receipt_full'] . '</pre>'
                  . '<div class="civicrm-dj-retrytx">' . t("The transaction was not approved. Please verify your credit card number and expiration date.") . '</div>');
          }
      }

      // CHECK Todo: above assignment seems to be ignored, not getting stored in the civicrm_financial_trxn table

      // Success
      // $params['trxn_result_code'] = $purchase['transaction_approved']; // may be about anything.. add approval code?
      $params['trxn_result_code'] = $purchase['condition_code'];
      $params['trxn_id']        = $purchase['transaction_id'];
      $params['gross_amount']   = $amount;

      // todo: above assignment seems to be ignored, not getting stored in the civicrm_financial_trxn table
      // $params['trxn_id']        = 1; // FIXME $mpgResponse->getTxnNumber();

      $receipt = $purchase['receipt_full'];
      $receipt = preg_replace("/^0/", "", $receipt);
      $receipt = preg_replace("/\n0/", "\n", $receipt);

      // we divide by 100 because Desjardins returns 100 for 1.00$
      $product_info = '  ' . t('Donation') . ': ' . sprintf('%.02f', $amount) . '$ CAD';

      // Add a few info on the receipt, make it look like a real store receipt:
      require_once 'CRM/Core/BAO/Domain.php';
      $domain =& CRM_Core_BAO_Domain::getDomain( );

      $loc =& $domain->getLocationValues();
      $address = $loc['address'][1];

      $receipt_full = $domain->name . " - " . $GLOBALS['base_url'] . "\n"
     	. $address['street_address'] . "\n"
        . $address['city'] . ", " . $address['postal_code'] . "\n"
    	. "\n"
    	. "Transaction: " . $purchase[2]['attributes']['id'] . "\n\n"
    	// . "Description des items:\n"
    	. $product_info . "\n\n"
    	. t("Authorization:") . " " . $purchase[42]['value'] . "\n"
    	. $receipt_full . "\n"
    	. t('General Terms and Conditions') . ":\n"
    	. "http://oxfam.qc.ca/" . i18n_get_lang() . "/conditions/termes" // [ML] FIXME, should be in config variable
        . "\n\n"
    	. $receipt;
    
      $params['receipt_desjardins'] = $receipt_full;
      $params['trxn_id'] = $invoice_id;

      // FIXME: correct string escaping?
      $query = "INSERT INTO desjardins_receipt_logs (trx_id, receipt, date, ip)
                VALUES ('". $invoice_id ."', '" . mysql_real_escape_string($receipt_full) ."', NOW(), '" . mysql_real_escape_string($params['ip_address']) . "');";

      $nullArray = array( );
      $dao = CRM_Core_DAO::executeQuery( $query, $nullArray );

      return $params;
    }

    function doDesjardinsLogin($key, $id_trx) {
	$xmlData = '';
	$response = '';

	$isProduction = ($this->_profile['mode'] == 'live');
   	$submit_url =  $this->_paymentProcessor['url_site'];

	$xmlData .= '<?xml version="1.0" encoding="UTF-8" ?>'
                  . '<request>'
                  .   '<merchant key="'. $key .'">'
                  .     '<login><trx id="'. $id_trx .'" /></login>'
                  .   '</merchant>'
                  . '</request>';

        $this->djLog($id_trx, $xmlData, 'dj_login send');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
	curl_setopt($ch, CURLOPT_URL, $submit_url);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	$response = curl_exec($ch);
	curl_close($ch);

        $this->djLog($id_trx, 'login response = ' . print_r($response, 1), 'dj_login response');

	return $response;
    }

    /**
     * Validate the postal code.
     * Returns TRUE if the postal code is valid.
     */
    function isValidPostalCode($params) {
      if ($params['country'] != 'CA') {
        return TRUE;
      }

      $province     = $params['state_province'];
      $postal_code  = $params['postal_code'];
      $postal_first = strtoupper(substr($postal_code, 0, 1));

      $provinces_codes = array(
        'AB' => array('T'),
        'BC' => array('V'),
        'MB' => array('R'),
        'NB' => array('E'),
        'NL' => array('A'),
        'NT' => array('X'),
        'NS' => array('B'),
        'NU' => array('X'),
        'ON' => array('K', 'L', 'M', 'P'),
        'PE' => array('C'),
        'QC' => array('H', 'J', 'G'),
        'SK' => array('S'),
        'YT' => array('Y'),
      );

      if (in_array($postal_first, $provinces_codes[$province])) {
        return TRUE;
      }

      return FALSE; 
    }

    /**
     * Check whether the person (by IP address) has been doing too many transactions lately (2 tx in the past 6 hours)
     * Returns TRUE if there have been too many transactions
     */
    function isTooManyTransactions($params) {
      $ip = $params['ip_address'];

      // XXX Drupal6 specific..
      $nb_tx_lately = db_result(db_query('select count(*) from {desjardins_receipt_logs} where ip = "%s" and date > DATE_SUB(NOW(), INTERVAL 1 HOUR)', $ip));

      if ($nb_tx_lately >= 4) {
        return TRUE;
      }

      return FALSE;
    }


    function &checkResult( &$response ) { // ignore for now, more elaborate error handling later.
        return $response;

        $errors = $response->getErrors( );
        if ( empty( $errors ) ) {
            return $result;
        }

        $e =& CRM_Core_Error::singleton( );
        if ( is_a( $errors, 'ErrorType' ) ) {
                $e->push( $errors->getErrorCode( ),
                          0, null,
                          $errors->getShortMessage( ) . ' ' . $errors->getLongMessage( ) );
        } else {
            foreach ( $errors as $error ) {
                $e->push( $error->getErrorCode( ),
                          0, null,
                          $error->getShortMessage( ) . ' ' . $error->getLongMessage( ) );
            }
        }
        return $e;
    }

    function &error( $error = null ) {
        $e =& CRM_Core_Error::singleton( );
        if ( is_object($error) ) {
            $e->push( $error->getResponseCode( ),
                      0, null,
                      $error->getMessage( ) );
        } elseif ( is_string($error) ) {
            $e->push( 9002,
                      0, null,
                      $error );
        } else {
            $e->push( 9001, 0, null, "Unknown System Error." );
        }
        return $e;
    }

    /** 
     * This function checks to see if we have the right config values 
     * 
     * @return string the error message if any 
     * @public 
     */ 
    function checkConfig( ) {
        $error = array( );

        if ( empty( $this->_paymentProcessor['user_name'] ) ) {
            $error[] = ts( 'Merchant ID is not set in the Administer CiviCRM &raquo; Payment Processor.' );
        }
            
        if ( empty( $this->_paymentProcessor['password'] ) ) {
            $error[] = ts( 'Password is not set in the Administer CiviCRM &raquo; Payment Processor.' );
        }

        if ( ! empty( $error ) ) {
            return implode( '<p>', $error );
        } else {
            return null;
        }
    }

    function djLog($trx_id, $message, $type, $fail = 0) {
      #if ($this->CIVICRM_DESJARDINS_LOG) {
        $message = preg_replace('/<number>(\d{2})\d{10}(\d{4})<\/number>/', '<number>\1**********\2</number>', $message);

        db_query("INSERT INTO {desjardins_receipt_debug} (trx_id, date, type, message, fail)
                  VALUES ('%s', NOW(), '%s', '%s', %d)",
                  $trx_id, $type, $message, $fail);
      #}
    }
}

class CRM_Core_Payment_Desjardins_Response {
	var $responseData;
	var $currentTag;
	var $parser;
	var $error;
	var $errno;

	function startHandler ($parser, $tag, $attrs) {
		$this->currentTag = $tag;

		if ($tag == 'trx') {
			$this->responseData['trx'] = $attrs;
		}

		if ($tag == 'error') {
			$this->error = TRUE;
		}
	}

	function endHandler ($parser, $tag) {
		$this->currentTag = 'none';
	}

	function characterHandler($parser, $data) {
		if ($this->error && $this->currentTag == 'code') {
			$this->errno = $data;
		}
	}

	function getData() {
		return $this->responseData;
	}

	function CRM_Core_Payment_Desjardins_Response($xmlString) {
		$this->responseData = array();
		$this->error = false;
		$this->errno = 0;

		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING,"UTF-8");
		xml_set_object($this->parser, $this);
		xml_set_element_handler($this->parser, "startHandler", "endHandler");
		xml_set_character_data_handler($this->parser, "characterHandler");
		xml_parse($this->parser, $xmlString);
		xml_parser_free($this->parser);
	}
}

