<?php

/*
Plugin Name: WooCommerce PayZippy Payment Gateway
Description: Extends WooCommerce with PayZippy Payment Gateway.
Version: 1.0.7
Author: PayZippy
*/
require('lib/Constants.php');
require_once('lib/ChargingRequest.php');
require_once('lib/ChargingResponse.php');

add_action('plugins_loaded', 'woocommerce_payzippy_init', 0);

function woocommerce_payzippy_init()
{

    if (!class_exists('WC_Payment_Gateway')) return;

    /*
    Uncomment this to show 'msg' key when present in the GET parameters.

    if ($_GET['msg'] != '') {
        add_action('the_content', 'showzippyMessage');
    }

    function showzippyMessage($content)
    {
        return '<div class="woocommerce-' . htmlentities($_GET['type']) . '">' .
        htmlentities(urldecode($_GET['msg'])) .
        '</div>' .
        $content;
    }
    */

    /**
     * PayZippy Payment Gateway
     *
     * @class          WC_PayZippy
     * @extends        WC_Payment_Gateway
     * @version        1.0.5
     */
    class WC_PayZippy extends WC_Payment_Gateway
    {
        protected $msg = array();

        /**
         *  Constructor for the gateway
         */
        public function __construct()
        {
            $this->id = 'payzippy';
            $this->method_title = 'PayZippy';
            $this->icon = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/images/logo.png';
            $this->has_fields = false;

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];

            $this->redirect_page_id = $this->settings['redirect_page_id'];

            // Get all the payment methods & banks available, configured from the admin settings page.
            /*$this->payment_methods_available = $this->settings['payment_methods_available'];
            $this->net_banks_available = $this->settings['net_banks_available'];
            $this->emi_banks_available = array(
                "3" => $this->settings['emi_banks_3'],
                "6" => $this->settings['emi_banks_6'],
                "9" => $this->settings['emi_banks_9'],
                "12" => $this->settings['emi_banks_12'],
            ); */

            // Function called on Charging API Callback.
            add_action('init', array($this, 'check_payzippy_response'));
            add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'check_payzippy_response'));

            if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            } else {
                add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
            }
            add_action('woocommerce_receipt_payzippy', array($this, 'receipt_page'));
        }

        /**
         * Initialise Gateway Settings Form Fields
         */
        function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable / Disable',
                    'type' => 'checkbox',
                    'label' => 'Enable PayZippy Payment Gateway for WooCommerce.',
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'description' => 'This will be displayed as payment method name on checkout.',
                    'default' => 'PayZippy',
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => 'Description:',
                    'type' => 'textarea',
                    'default' => 'Pay securely by Credit or Debit card or internet banking through PayZippy (from Flipkart) Secure Servers.',
                ),
                'merchant_id' => array(
                    'title' => 'Merchant ID',
                    'type' => 'text',
                    'desc_tip' => true,
                    'description' => 'Given to Merchant by PayZippy.',
                ),
                'merchant_key_id' => array(
                    'title' => 'Merchant Key ID',
                    'type' => 'text',
                    'desc_tip' => true,
                    'description' => 'Given to Merchant by PayZippy.',
                ),
                'secret_key' => array(
                    'title' => 'Secret Key',
                    'type' => 'text',
                    'desc_tip' => true,
                    'description' => 'Given to Merchant by PayZippy.',
                ),
                'charging_url' => array(
                    'title' => 'PayZippy API URL',
                    'type' => 'text',
                    'desc_tip' => true,
                    'description' => 'PayZippy Charging API URL',
                    'default' => 'https://www.payzippy.com/payment/api/charging/v1',
                ),
                'ui_mode' => array(
                    'title' => 'UI Integration Mode',
                    'type' => 'select',
                    'options' => array(
                        "IFRAME" => "IFRAME",
                        "REDIRECT" => "REDIRECT"),
                ),
                'hash_method' => array(
                    'title' => 'Hash Method',
                    'type' => 'select',
                    'options' => array(
                        "MD5" => "MD5",
                        "SHA256" => "SHA256"),
                ),
                /*'payment_methods_available' => array(
                    'title' => __('Payment Methods to Enable', 'woothemes'),
                    'type' => 'multiselect',
                    'description' => __('Select the payment methods to enable', 'woothemes'),
                    'options' => PZ_Constants::PAYMENT_METHODS(),
                    'desc_tip' => true,
                ),
                'net_banks_available' => array(
                    'title' => __('Allowed banks for Net Banking', 'woothemes'),
                    'type' => 'multiselect',
                    'description' => __('Select the banks to enable for Net Banking', 'woothemes'),
                    'desc_tip' => true,
                    'options' => PZ_Constants::BANK_NAMES(),
                ),
                'emi_banks_3' => array(
                    'title' => __('Allowed banks for 3 months EMI', 'woothemes'),
                    'type' => 'multiselect',
                    'description' => __('Select the banks to enable for EMI with 3 months', 'woothemes'),
                    'desc_tip' => true,
                    'options' => PZ_Constants::BANK_NAMES()
                ),
                'emi_banks_6' => array(
                    'title' => __('Allowed banks for 6 months EMI', 'woothemes'),
                    'type' => 'multiselect',
                    'description' => __('Select the banks to enable for EMI with 6 months', 'woothemes'),
                    'desc_tip' => true,
                    'options' => PZ_Constants::BANK_NAMES()
                ),
                'emi_banks_9' => array(
                    'title' => __('Allowed banks for 9 months EMI', 'woothemes'),
                    'type' => 'multiselect',
                    'description' => __('Select the banks to enable for EMI with 9 months', 'woothemes'),
                    'desc_tip' => true,
                    'options' => PZ_Constants::BANK_NAMES()
                ),
                'emi_banks_12' => array(
                    'title' => __('Allowed banks for 12 months EMI', 'woothemes'),
                    'type' => 'multiselect',
                    'description' => __('Select the banks to enable for EMI with 12 months', 'woothemes'),
                    'desc_tip' => true,
                    'options' => PZ_Constants::BANK_NAMES()
                ),*/
                'redirect_page_id' => array(
                    'title' => 'Return Page',
                    'type' => 'select',
                    'options' => $this->get_pages('Select Page'),
                    'description' => 'URL of success page',
                    'desc_tip' => true,
                )
            );
        }

        /**
         * Generates the HTML for the admin settings page
         */
        function admin_options()
        {
            echo '<h3>' . 'PayZippy Payment Gateway' . '</h3>';
            echo '<p>' . 'PayZippy is a smart payment product built by Flipkart, which makes it extremely safe and easy to pay online.' . '</p>';
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }

        /*
         * Generates the HTML for the PayZippy payment fields
         * during the checkout process.
         *
         * This includes Payment Method, Bank Name, EMI months
         * and inline script to display bank name and emi months
         * fields appropriately.
         */
        //can be commented
        function payment_fields()
        {
	    if($this->description) echo wpautop(wptexturize($this->description));
            //$this->template_payment_methods($this->payment_methods_available);
            //$this->template_emi_months();
            //$this->template_bank_names();
            //$this->template_payment_fields_script($this->net_banks_available, $this->emi_banks_available);
        }

        /**
         * Process the payment field and redirect to checkout/pay page.
         *
         * @param $order_id
         * @return array
         */
        function process_payment($order_id)
        {
            $order = new WC_Order($order_id);

            // Persist the payment method, bank name and emi months as private custom fields.
            update_post_meta($order->id, '_pz_payment_method', mysql_real_escape_string($_POST['pz_payment_method']));
            //update_post_meta($order->id, '_pz_bank_name', mysql_real_escape_string($_POST['pz_bank_name']));
            //update_post_meta($order->id, '_pz_emi_months', mysql_real_escape_string($_POST['pz_emi_months']));

            // Redirect to checkout/pay page
            return array(
                'result' => 'success',
                'redirect' => add_query_arg('order', $order->id,
                    add_query_arg('key', $order->order_key, $order->get_checkout_payment_url(true)))
            );
        }

        /**
         * Output for the checkout/pay page
         *
         * @param $order_id
         * @return void
         */
        function receipt_page($order_id)
        {
            echo $this->validate_and_charge($order_id);
        }

        /**
         * Validate the Charging request and return the HTML to display for the given order_id
         *
         * @param $order_id
         * @return string
         */
        function validate_and_charge($order_id)
        {
            $order = new WC_Order($order_id);

            // Callback url

            $redirect_url = get_site_url() . '?wc-api' ."=". get_class($this) ;

            $payment_method = get_post_meta($order_id, '_pz_payment_method', true);

            // Append the payment attempts to Transaction ID
            $retries = get_post_meta($order_id, '_pz_attempts', true);
            if (strlen($retries) < 1 ) {
                $retries = 1;
            }
            
            update_post_meta($order->id, '_pz_attempts', $retries + 1);
            $merchant_transaction_id = $order_id . '_' . $retries;

            // Building the Charging Request object
            $pz_charging = new ChargingRequest($this->settings);
            $pz_charging->set_buyer_email_address($order->billing_email)
                ->set_buyer_phone_no($order->billing_phone)
                ->set_buyer_unique_id($order->customer_user)
                ->set_billing_name($order->billing_first_name . " " . $order->billing_last_name)
                ->set_billing_address($order->billing_address . " " . $order->billing_address_1 . " " . $order->billing_address_2)
                ->set_billing_city($order->billing_city)
                ->set_billing_state($order->billing_state)
                ->set_billing_country($order->billing_country)
                ->set_billing_zip($order->billing_postcode)
                ->set_shipping_address($order->shipping_address . " " . $order->shipping_address_1 . " " . $order->shipping_address_2)
                ->set_shipping_city($order->shipping_city)
                ->set_shipping_state($order->shipping_state)
                ->set_shipping_country($order->shipping_country)
                ->set_shipping_zip($order->shipping_postcode)
                ->set_merchant_transaction_id($merchant_transaction_id)
                ->set_transaction_amount($order->order_total * 100)
                ->set_payment_method($payment_method)
                ->set_callback_url($redirect_url)
                ->set_source("woocommerce-1.0.3");;

            /* Set bank name and/or emi months
            switch ($payment_method) {
                case PZ_Constants::PAYMENT_MODE_NET:
                    $pz_charging->set_bank_name(get_post_meta($order_id, '_pz_bank_name', true))
                        ->set_ui_mode(PZ_Constants::UI_MODE_REDIRECT);
                    break;

                case PZ_Constants::PAYMENT_MODE_EMI:
                    $pz_charging->set_bank_name(get_post_meta($order_id, '_pz_bank_name', true))
                        ->set_emi_months(get_post_meta($order_id, '_pz_emi_months', true));
                    break;
            }
            */
            // Get charging array
            $pz_charging_array = $pz_charging->charge();

            // Check if charging request was valid
            if ($pz_charging_array['status'] != 'OK') {
                return '<ul class="woocommerce-error">
                            <li>' . $pz_charging_array['error_message'] . '</li>
                        </ul>
                        <a href="' . get_permalink(get_option('woocommerce_checkout_page_id')) . '">
                        Go back to Checkout Page</a>';

            }

            // Return iframe or hidden HTML form.
            switch ($pz_charging->get_ui_mode()) {
                case PZ_Constants::UI_MODE_IFRAME:
                    return $this->generate_payzippy_iframe($pz_charging_array);

                case PZ_Constants::UI_MODE_REDIRECT:
                    return $this->generate_payzippy_form($pz_charging_array);
            }
        }

        /**
         * Generate PayZippy Charging IFRAME
         *
         * @param $pz_charging_array
         * @return string
         */
        function generate_payzippy_iframe($pz_charging_array)
        {
            return '<iframe src="' . $pz_charging_array['url'] . '"height="450px" width="100%" style="border:none;"></iframe>';
        }

        /**
         * Generate PayZippy Charging form that auto submits
         *
         * @param $pz_charging_array
         * @return string
         */
        function generate_payzippy_form($pz_charging_array)
        {
            global $woocommerce;

            $payzippy_args_array = array();
            foreach ($pz_charging_array['params'] as $key => $value) {
                $payzippy_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
            }

            $woocommerce->add_inline_js('
                jQuery(function(){
                    console.log("inside inline pz js");
                    // jQuery("body").block({
                    //     message: "' . 'Thank you for your order. We are now redirecting you to PayZippy (Flipkart) to make payment.' . '",
                    //     overlayCSS:
                    //     {
                    //         background:     "#000",
                    //         opacity:        0.6
                    //     },
                    //     css: {
                    //         padding:        "20px",
                    //         zindex:         "9999999",
                    //         textAlign:      "center",
                    //         color:          "#555",
                    //         border:         "3px solid #aaa",
                    //         backgroundColor:"#fff",
                    //         cursor:         "wait",
                    //         lineHeight:     "32px"
                    //     }
                    // });
                    jQuery("#payzippy_payment_form").submit();
                });
            ');

            return '<form action="' . $pz_charging_array['url'] . '" method="post" id="payzippy_payment_form">'
            . implode('', $payzippy_args_array) . '</form>';
        }

        /**
         * Check the PayZippy Charging API response, validate it, update DB
         */
        function check_payzippy_response()
        {
            //require('lib/Constants.php');
            global $woocommerce;

            // Instantiate the ChargingResponse class.
            $pz_response = new ChargingResponse(array_merge($_POST,$_GET), $this->settings);

            if ($pz_response->get_merchant_transaction_id() != '') {
                $transaction_id = $pz_response->get_merchant_transaction_id();
                $order = new WC_Order($transaction_id);
                $transaction_key = $order->order_key;

                if ($pz_response->validate()) {
                    if ($pz_response->get_transaction_response_code() == PZ_Constants::RESPONSE_SUCCESS) {
                        // Payment Successful
                        $message = PZ_Constants::PAYMENT_SUCCESS;
                        $class = 'message';
                        $order->payment_complete();
                        $woocommerce->cart->empty_cart();
                    } else if ($pz_response->get_transaction_response_code() == PZ_Constants::RESPONSE_PENDING) {
                        // Payment Response Pending.
                        $order->update_status('on-hold');
                        $message = PZ_Constants::PAYMENT_ONHOLD;
                        $class = 'message';
                        $woocommerce->cart->empty_cart();
                    } else if ($pz_response->get_transaction_response_code() == PZ_Constants::RESPONSE_INITIATED) {
                        // Payment Response Initiated.
                        $order->update_status('pending');
                        $message = PZ_Constants::PAYMENT_INITIATED;
                        $class = 'message';
                        $woocommerce->cart->empty_cart();
                    } else {
                        // Payment Failed.
                        $order->update_status('failed');
                        $class = 'error';
                        $message = PZ_Constants::PAYMENT_FAILED;
                    }
                } else {
                    // Hash Validation Failed.
                    $message = PZ_Constants::PAYMENT_ILLEGAL;
                    $order->update_status('failed');
                    $order->add_order_note($message);
                    $class = 'error';
                }

                $order->add_order_note($message);
                $this->update_order_info($order, $pz_response);
            } else {
                $class = 'error';
                $message = 'Error: 747. Contact us with this error code and transaction details.';
            }

            $callback_url = ($this->redirect_page_id == "" || $this->redirect_page_id == 0) ? get_site_url() . "/" : get_permalink($this->redirect_page_id);
        if (version_compare(WOOCOMMERCE_VERSION, '2.1.0', '>=')) {
            $callback_url = add_query_arg(array('view-order' => $transaction_id,
                    'key' => $transaction_key,
                    'msg' => urlencode($message),
                    'type' => $class),
                $callback_url);
        }
        else {
            $callback_url = add_query_arg(array('order' => $transaction_id,
                    'key' => $transaction_key,
                    'msg' => urlencode($message),
                    'type' => $class),
                $callback_url);
        }
            wp_redirect($callback_url);
            exit;

        }

        /**
         * Update the Order ID with the response from PayZippy Charging API
         *
         * @param \WC_Order $order Order to update
         * @param \ChargingResponse $pz_response PayZippy Charging Response
         */
        private function update_order_info(WC_Order $order, ChargingResponse $pz_response)
        {
            $pz_order_note = "PayZippy Response Summary : {";
            $pz_order_note .= " 'PayZippy Transaction ID' : '" . $pz_response->get_payzippy_transaction_id() . "', ";

            switch ($pz_response->get_payment_method()) {
                case PZ_Constants::PAYMENT_MODE_NET:
                    $payment_method = PZ_Constants::BANK_NAMES($pz_response->get_bank_name()) . ' ' .
                        PZ_Constants::PAYMENT_METHODS($pz_response->get_payment_method());
                    break;

                case PZ_Constants::PAYMENT_MODE_EMI:
                    $payment_method = PZ_Constants::BANK_NAMES($pz_response->get_bank_name()) . ' ' .
                        $pz_response->get_emi_months() . ' ' .
                        PZ_Constants::PAYMENT_METHODS($pz_response->get_payment_method());
                    break;

                default:
                    $payment_method = $pz_response->get_payment_method();
            }

            $pz_order_note .= " 'Payment Method' : '" . $payment_method . "', ";
            update_post_meta($order->id, 'Payment Method',
                mysql_real_escape_string($payment_method));

            $pz_order_note .= " 'Transaction Status' : '" . $pz_response->get_transaction_status() . "', ";
            update_post_meta($order->id, 'Transaction Status',
                mysql_real_escape_string($pz_response->get_transaction_status()));

            $pz_order_note .= " 'Transaction Response Code' : '" . $pz_response->get_transaction_response_code() . "', ";
            update_post_meta($order->id, 'Transaction Response Code',
                mysql_real_escape_string($pz_response->get_transaction_response_code()));

            $pz_order_note .= " 'Transaction Response Message' : '" . $pz_response->get_transaction_response_message() . "', ";
            update_post_meta($order->id, 'Transaction Response Message',
                mysql_real_escape_string($pz_response->get_transaction_response_message()));

            $pz_order_note .= " 'Is International' : '" . $pz_response->get_is_international() . "', ";
            update_post_meta($order->id, 'Is International',
                mysql_real_escape_string($pz_response->get_is_international()));

            $pz_order_note .= " 'Fraud Action' : '" . $pz_response->get_fraud_action() . "' }";
            update_post_meta($order->id, 'Fraud Action',
                mysql_real_escape_string($pz_response->get_fraud_action()));

            $order->add_order_note(mysql_real_escape_string($pz_order_note));
        }

        /*
         * Template for Payment Methods select input
         
        function template_payment_methods($payment_methods)
        {
            ?>
            <p class="form-row chzn-container-single">
                <label for="pz_payment_method">Payment Method <abbr class="required" title="required">*</abbr></label>
                <select name="pz_payment_method" id="pz_payment_method" class="chzn-single">
                    <?php
                    foreach ($payment_methods as $method)
                        echo '<option value="' . $method . '">' . PZ_Constants::PAYMENT_METHODS($method) . '</option>';
                    ?>
                </select>
            </p>
        <?php
        }

        
         * Template for EMI Months select input
        function template_emi_months()
        {
            ?>
            <p class="form-row chzn-container-single pz_emi_months" style="display: none">
                <label for="pz_emi_months">EMI Months <abbr class="required" title="required">*</abbr></label>
                <select name="pz_emi_months" id="pz_emi_months" class="chzn-single">
                    <option value="3">3 months</option>
                    <option value="6">6 months</option>
                    <option value="9">9 months</option>
                    <option value="12">12 months</option>
                </select>
            </p>
        <?php
        }

         * Template for Bank Names select input
        function template_bank_names()
        {
            ?>
            <p class="form-row chzn-container-single pz_bank_fieldset" style="display: none">
                <label for="pz_bank_name">Bank Name <abbr class="required" title="required">*</abbr></label>
                <select name="pz_bank_name" id="pz_bank_name" class="chzn-single">
                </select>
            </p>
        <?php
        }

         * Show EMI months and valid Bank Names as per the Payment Method selected

        function template_payment_fields_script($netbanking_banks, $emi_banks)
        {
            $netbanking_options = "";
            if (!empty($netbanking_options)) {
                foreach ($netbanking_banks as $bank) {
                    $netbanking_options .= '<option value="' . $bank . '">' . PZ_Constants::BANK_NAMES($bank) . '</option>';
                }
            }

            $emi_banks_options = "";
            foreach ($emi_banks as $months => $banks) {
                if (!empty($banks)){
                    foreach ($banks as $bank) {
                        $options .= '<option value="' . $bank . '">' . PZ_Constants::BANK_NAMES($bank) . '</option>';
                    }
                }
                $emi_banks_options[$months] = $options;
            }

            ?>
*/

//        <?php
//        }

        function get_pages($title = false, $indent = true)
        {
            $wp_pages = get_pages('sort_column=menu_order');
            $page_list = array();
            if ($title) $page_list[] = $title;
            foreach ($wp_pages as $page) {
                $prefix = '';
                if ($indent) {
                    $has_parent = $page->post_parent;
                    while ($has_parent) {
                        $prefix .= ' - ';
                        $next_page = get_post($has_parent);
                        $has_parent = $next_page->post_parent;
                    }
                }
                $page_list[$page->ID] = $prefix . $page->post_title;
            }
            return $page_list;
        }

    }

    function woocommerce_add_payzippy_gateway($methods)
    {
        $methods[] = 'WC_PayZippy';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_payzippy_gateway');
}

?>
