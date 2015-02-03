<?php

namespace IdnoPlugins\Chrome {

    class Main extends \Idno\Common\Plugin {

	function registerPages() {
	    // Register settings page
	    \Idno\Core\site()->addPageHandler('account/chrome', '\IdnoPlugins\Chrome\Pages\Account');

	    // Handlers
	    \Idno\Core\site()->addPageHandler('chrome/download/?', '\IdnoPlugins\Chrome\Pages\Download');

	    /** Template extensions */
	    // Add menu items to account screen
	    \Idno\Core\site()->template()->extendTemplate('account/menu/items', 'account/chrome/menu');
	}

	/** DER/PEM conversion from https://php.net/manual/en/ref.openssl.php#74188 */
	static function pem2der($pem_data) {
	    $begin = "CERTIFICATE-----";
	    $end = "-----END";
	    $pem_data = substr($pem_data, strpos($pem_data, $begin) + strlen($begin));
	    $pem_data = substr($pem_data, 0, strpos($pem_data, $end));
	    $der = base64_decode($pem_data);
	    return $der;
	}

	static function der2pem($der_data) {
	    $pem = chunk_split(base64_encode($der_data), 64, "\n");
	    $pem = "-----BEGIN CERTIFICATE-----\n" . $pem . "-----END CERTIFICATE-----\n";
	    return $pem;
	}

	/*	 * **** */
    }

}