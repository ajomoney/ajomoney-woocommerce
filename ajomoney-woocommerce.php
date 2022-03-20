<?php
/**
 * Plugin Name: AjoMoney Gateway For WooCommerce
 * Plugin URI: https://ajo.money/business
 * Description: WooCommerce buy now pay later and checkout gateway for AjoMoney
 * Version: 1.0.0
 * Author: AjoPay Financial Technology Limited
 * Author URI: https://ajo.money
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ajomoney-woocommerce
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins') ))) return;

add_action('plugins_loaded', 'ajomoney_payment_init', 11);

function  ajomoney_payment_init() {
    if( class_exists('WC_Payment_Gateway')) {
        class WC_Ajomoney_Pay_Gateway extends WC_Payment_Gateway {
            public function  __construct() {
                $this->id = 'ajomoney_payment';
                $this->icon = apply_filters( 'woocommerce_ajomoney_icon', plugins_url('/assets/icon.png', __FILE__) );
                $this->has_fields = false;
                $this->method_title = __( 'AjoMoney Payment', 'ajomoney-woocommerce' );
                $this->method_description = __( 'Buy now pay later and full payment checkout gateway for AjoMoney', 'ajomoney-woocommerce' );

                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');
                $this->instruction = $this->get_option('instruction');

                $this->init_form_fields();
                $this->init_settings();

                add_action( 'woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options') );
            }

            public function init_form_fields() {
                $this->form_fields = apply_filters( 'woo_ajomoney_pay_fields', array(
                    'enabled' => array(
                        'title' => __( 'Enabled/Disabled', 'ajomoney-woocommerce' ),
                        'type' => 'checkbox',
                        'label' => __( 'Enable or Disable AjoMoney Payment', 'ajomoney-woocommerce' ),
                        'default' => 'no'
                    ),
                    'title' => array(
                        'title' => __( 'AjoMoney Payments Gateway', 'ajomoney-woocommerce' ),
                        'type' => 'text',
                        'label' => __( 'Add a new title for the AjoMoney Payment Gateway.', 'ajomoney-woocommerce' ),
                        'default' => __( 'AjoMoney Payments Gateway', 'ajomoney-woocommerce' ),
                        'desc_tip' => true,
                        'description' => __( 'AjoMoney Payments Gateway', 'ajomoney-woocommerce' ),
                    ),

                    'description' => array(
                        'title' => __( 'AjoMoney Payments Gateway Description', 'ajomoney-woocommerce' ),
                        'type' => 'textarea',
                        'label' => __( 'Add a new title for the AjoMoney Payment Gateway.', 'ajomoney-woocommerce' ),
                        'default' => __( 'AjoMoney Payments Gateway', 'ajomoney-woocommerce' ),
                        'desc_tip' => true,
                        'description' => __( 'AjoMoney Payments Gateway', 'ajomoney-woocommerce' ),
                    ),
                    'instruction' => array(
                        'title' => __( 'Instruction', 'ajomoney-woocommerce' ),
                        'type' => 'textarea',
                        'default' => __( '', 'ajomoney-woocommerce' ),
                        'desc_tip' => true,
                        'description' => __( 'Instruction is here', 'ajomoney-woocommerce' ),
                    ),
                ) );
            }

            public function process_payments($order_id) {
                $order_id = wc_get_order($order_id);

                $order->update_status('on-hold', __('Awaiting AjoMoney payment', 'ajomoney-woocommerce' ));

                $this->clear_ajomoney_payment_api();

                $order->reduce_order_stock();

                WC()->cart->empty_cart();

                return  array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order)
                );
            }

            public function clear_ajomoney_payment_api() {

            }
        }
    }
}

add_filter( 'woocommerce_payment_gateways', 'add_to_ajomoney_payment_gateway');

function add_to_ajomoney_payment_gateway($gateways) {

    $gateways[] = 'WC_Ajomoney_Pay_Gateway';
    return $gateways;
}