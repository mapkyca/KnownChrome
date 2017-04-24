<?php

    namespace IdnoPlugins\Chrome\Pages {

	use Idno\Common\Page;
        use Idno\Entities\File;
	
        class Download extends \Idno\Common\Page
        {

	    private function build($dir = false) {
		set_time_limit(0);  // Switch off the time limit for PHP
		
                // Prepare a unique name for the archive
                $name = md5(time() . rand(0,9999) . \Idno\Core\site()->config()->getURL());

                if (!is_dir($dir)) {
                    $dir = sys_get_temp_dir();
                }
                if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
                    $dir .= DIRECTORY_SEPARATOR;
                }
		
		if (!@mkdir($dir . $name)) {
                    return false;
                }
		
		// Build manifest
		file_put_contents($dir . $name . DIRECTORY_SEPARATOR . 'manifest.json', json_encode([
		    'name' => \Idno\Core\site()->config()->title,
		    'description' => 'Chrome plugin for Known on ' . \Idno\Core\site()->config()->title,
		    'author' => 'Marcus Povey <marcus@marcus-povey.co.uk>',
		    'version' => '1.1',
		    'manifest_version' => 2,
		    
		    "run_at" => "document_start",
		    'all_frames' => true,
		    
		    'permissions' => [
			'*://'.\Idno\Core\site()->config()->host.'/*',
			'tabs'
		    ],
		    
		    'browser_action' => [
			'default_icon' => 'known.png',
			'default_popup' => 'known.html'
		    ],
		]));
		
		// Build popup
		$t = \Idno\Core\site()->template();
		file_put_contents($dir . $name . DIRECTORY_SEPARATOR . 'known.html', $t->draw('package/chrome/known_html'));
		file_put_contents($dir . $name . DIRECTORY_SEPARATOR . 'known.js', $t->draw('package/chrome/known_js'));
		
		// Icons	
		if ($site_icons = \Idno\Core\site()->getSiteIcons()) {
		    $logo = $site_icons['defaults']['default_64'];
		}
		if (!$logo) {
		    $logo = \Idno\Core\site()->config()->getDisplayURL() . 'gfx/logos/logo_k_64.png';
		}
		if (!file_put_contents($dir . $name . DIRECTORY_SEPARATOR . 'known.png', file_get_contents($logo))) {
		    throw new \Exception("Known.png could not be generated from $logo");
		}
		
		
		// Build zip
		if (!class_exists('PharData')) {
                    throw new \Exception('Phar archive extension not installed');
                }
		
		$filename = str_replace('.','_',\Idno\Core\site()->config()->host)."_for_chrome";

		$archive = new \PharData($dir . $filename . '.zip');
		$archive->buildFromDirectory($dir . $name . DIRECTORY_SEPARATOR);
		
		if (!is_callable('openssl_pkey_new')) {
		
		    \Idno\Core\site()->session()->addErrorMessage("OpenSSL is not installed, so I couldn't generate a CRX file, returing installable .zip instead.");
		    
		    return $archive->getPath();
		} else {
		    // Generate a CRX (https://stackoverflow.com/questions/5013263/create-google-chrome-crx-file-with-php)
		 
		    // Build new keypair
		    $keypair = openssl_pkey_new(array(
			"private_key_bits" => 2048,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
		    ));
		    
		    if (!openssl_pkey_export($keypair, $keypair_pem))
			    throw new \Exception("Could not generate keypair");
		    
		    // Output pem
		    file_put_contents($dir."$name.pem", $keypair_pem);
		    
		    // Get public key
		    $pubkey=openssl_pkey_get_details($keypair);
		    $pubkey=$pubkey["key"];
		    
		    // Output pubkey in DER format
		    file_put_contents($dir."$name.der", \IdnoPlugins\Chrome\Main::pem2der($pubkey));
		    
		    // Sign archive
		    $pk = openssl_pkey_get_private(file_get_contents($dir."$name.pem"));
		    openssl_sign(file_get_contents($archive->getPath()), $signature, $pk, OPENSSL_ALGO_SHA1);
		    openssl_free_key($pk);
		    
		    # decode the public key
		    $pubkey = file_get_contents($dir."$name.der");//base64_decode(file_get_contents($dir."$name.pub"));
		    
		    # .crx package format:
		    #
		    #   magic number               char(4)
		    #   crx format ver             byte(4)
		    #   pub key lenth              byte(4)
		    #   signature length           byte(4)
		    #   public key                 string
		    #   signature                  string
		    #   package contents, zipped   string
		    #
		    # see http://code.google.com/chrome/extensions/crx.html
		    #
		    $fh = fopen($dir . $filename . '.crx', 'wb');
		    fwrite($fh, 'Cr24');                             // extension file magic number
		    fwrite($fh, pack('V', 2));                       // crx format version
		    fwrite($fh, pack('V', strlen($pubkey)));            // public key length
		    fwrite($fh, pack('V', strlen($signature)));      // signature length
		    fwrite($fh, $pubkey);                               // public key
		    fwrite($fh, $signature);                         // signature
		    fwrite($fh, file_get_contents($dir . $filename . '.zip')); // package contents, zipped
		    fclose($fh);
		    
		    return $dir . $filename . '.crx';
		}
	    }
	    
            function getContent()
            {
                $this->gatekeeper(); // Logged-in users only
		
		try {
		    if ($archive = $this->build()) {
			
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.basename($archive).'"'); 
			header('Content-Transfer-Encoding: binary');
			header('Connection: Keep-Alive');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($archive));
			
			readfile($archive);
			exit;
			
		    } else {
			throw new \Exception("Sorry, there was a problem building your plugin!");
		    }
		} catch (\Exception $ex) {
		    \Idno\Core\site()->session()->addErrorMessage($ex->getMessage());
		}
		
            }


        }

    }