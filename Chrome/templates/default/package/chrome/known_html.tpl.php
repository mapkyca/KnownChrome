<!DOCTYPE html>
<html>
    <head>
	<title><?= \Idno\Core\site()->config()->title; ?></title>
	<style>
	    body {
		min-width: 357px;
		overflow-x: hidden;
	    }

	    iframe {
		min-height: 400px;
		display: block;
	    }
	</style>
	<script src="known.js"></script>
    </head>
    <body>
	    <iframe id="iframe" src="" style="border: 0; width: 100%; height: 100%" frameborder="0" scrolling="no">Your browser doesn't support iframes</iframe>
    </body>
</html>