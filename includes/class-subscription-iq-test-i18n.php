<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://github.com/cafeconlecheymedialunas
 * @since      1.0.0
 *
 * @package    SubscriptionIqTest
 * @subpackage SubscriptionIqTest/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    SubscriptionIqTest
 * @subpackage SubscriptionIqTest/includes
 * @author     Mauro Gaitan <maurodeveloper86@gmail.com>
 */
class SubscriptionIqTesti18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'subscription-iq-test',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
