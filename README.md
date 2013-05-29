force-media-download
====================

This script makes the files residing in the current folder to be offered for download rather than loaded within the browser.

= Installation =
 
1) Create a folder especially dedicated for downloads within your document root (www, public_html, htdocs, httpdocs, httpsdocs) 
e.g. downloads.

2) Then upload the 2 files .htacess and index.php from this project within it

3) Link to the downloads as you normally would
e.g. http://yoursite.com/downloads/document.pdf

The index.php will capture the request and prompt the file for download.

Notes: 
1) All the files within that folder will be offered for download
2) It requires Apache + mod_rewrite in order to work.
