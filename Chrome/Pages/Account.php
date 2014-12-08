<?php

    /**
     * Chrome account page
     */

    namespace IdnoPlugins\Chrome\Pages {

        /**
         * Default class to serve Chrome-related account settings
         */
        class Account extends \Idno\Common\Page
        {

            function getContent()
            {
                $this->createGatekeeper(); // Logged-in users only
                $t = \Idno\Core\site()->template();
                $body = $t->draw('account/chrome');
                $t->__(array('title' => 'Chrome', 'body' => $body))->drawPage();
            }

        }
    }