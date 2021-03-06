function resizeIframe() {
    var obj = document.getElementById("iframe");
    
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}



document.addEventListener('DOMContentLoaded', function () {

    var frame = document.getElementById('iframe'); //document.createElement('iframe');
    frame.setAttribute('width', '100%');
    frame.setAttribute('height', '100%');
    frame.setAttribute('frameborder', '0');
    frame.setAttribute('id', 'iframe');
    frame.setAttribute('crossOrigin', 'use-credentials');

    document.body.appendChild(frame);
    
    frame.addEventListener('load', resizeIframe);
    //document.getElementById("iframe").addEventListener('load', resizeIframe);
    
    //chrome.tabs.getSelected(null, function(tab) {
    chrome.tabs.query({
	active: true,
	lastFocusedWindow: true
    }, function(tabs) {
	tab = tabs[0];
	
	document.getElementById('iframe').src = '<?= \Idno\Core\site()->config()->getDisplayURL(); ?>share?via=ff_social&share_url=' + encodeURIComponent(tab.url) +'&share_title=' + encodeURIComponent(tab.title);
    });

});
