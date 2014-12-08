<html>
    <head>
	<title><?= \Idno\Core\site()->config()->title; ?></title>
	<style>
	    body {
		min-width: 357px;
		overflow-x: hidden;
	    }

	    img {
		margin: 5px;
		border: 2px solid black;
		vertical-align: middle;
		width: 75px;
		height: 75px;
	    }
	</style>
	<script src="known.js"></script>
    </head>
    <body>
	<iframe id="iframe" src="<?= \Idno\Core\site()->config()->getDisplayURL(); ?>chrome/share" style="border: 0; width: 100%; height: 100%" frameborder="0" scrolling="no" >Your browser doesn't support iframes</iframe>
    </body>
</html>