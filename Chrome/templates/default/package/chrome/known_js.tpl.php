function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}

chrome.tabs.getSelected(null, function(tab) {
    document.getElementById('iframe').src = '<?= \Idno\Core\site()->config()->getDisplayURL(); ?>share?via=ff_social&share_url=' + encodeURIComponent(tab.url) +'&share_title=' + encodeURIComponent(tab.title);
});


document.addEventListener('DOMContentLoaded', function () {
    document.getElementById("iframe").addEventListener('load', resizeIframe);
});
