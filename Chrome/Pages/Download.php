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
		    'version' => '0.1',
		    'manifest_version' => 2,
		    
		    'permissions' => [
			\Idno\Core\site()->config()->getDisplayURL(),
			'tabs'
		    ],
		    
		    'browser_action' => [
			'default_icon' => 'known.png',
			'default_popup' => 'known.html'
		    ]
		]));
		
		// Build popup
		$t = \Idno\Core\site()->template();
		file_put_contents($dir . $name . DIRECTORY_SEPARATOR . 'known.html', $t->draw('package/chrome/known_html'));
		file_put_contents($dir . $name . DIRECTORY_SEPARATOR . 'known.js', $t->draw('package/chrome/known_js'));
		
		// Icons
		file_put_contents($dir . $name . DIRECTORY_SEPARATOR . 'known.png', file_get_contents(\Idno\Core\site()->config()->getDisplayURL() . 'gfx/logos/logo_k_64.png')); // TODO: Do this better once a site icon method is available
		
		
		// Build zip
		if (!class_exists('PharData')) {
                    throw new \Exception('Phar archive extension not installed');
                }
		
		$filename = str_replace('.','_',\Idno\Core\site()->config()->host)."_for_chrome.zip";
		
		$archive = new \PharData($dir . $filename);
                $archive->buildFromDirectory($dir . $name . DIRECTORY_SEPARATOR);
		
		return $archive->getPath();
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