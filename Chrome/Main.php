<?php

    namespace IdnoPlugins\Chrome {

        class Main extends \Idno\Common\Plugin {

            function registerPages() {
                // Register settings page
                \Idno\Core\site()->addPageHandler('account/chrome','\IdnoPlugins\Chrome\Pages\Account');

                // Handlers
		\Idno\Core\site()->addPageHandler('chrome/download/?','\IdnoPlugins\Chrome\Pages\Download'); 

                /** Template extensions */
                // Add menu items to account screen
                \Idno\Core\site()->template()->extendTemplate('account/menu/items','account/chrome/menu');
            }

        }

    }