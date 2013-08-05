force-media-download
====================

This script makes the files residing in the current folder to be offered for download rather than loaded within the browser.


Benefits
--------

* Have your media files not open in the browser window but offered for download
* The script includes last modified time and MD5 checksum for the file so if the file has been downloaded already
the script will output Not Modified header.
* Easy installation


Installation
-------------------------

1) Create a folder especially dedicated for downloads within your document root (www, public_html, htdocs, httpdocs, httpsdocs) 
e.g. downloads.

2) Then upload the 2 files .htacess and index.php from this project to that folder.

3) Link to the downloads as you normally would
e.g. http://yoursite.com/downloads/document.pdf

The index.php will capture all requests and prompt the file for download.

Notes: 
1) All the files within that folder will be offered for download
2) It requires Apache + mod_rewrite in order to work.
3) Currently, the force media download script supports files only within the current directory
i.e. if you have subfolders in the downloads folder it won't work. e.g. http://yoursite.com/downloads/folder2/document.pdf
4) Files without extensions are not supported e.g. if a file name doesn't have a "." in its name.


Troubleshooting
-------------------------

if you are getting errors: page not found or something else you can uncomment the line in the .htaccess and specify the directory
e.g. RewriteBase /downloads/


Support
-------------------------

If you find a bug or have a suggestion create an issue at github.


Other Products?
---------------

If you are curious what other products we have released visit: http://club.orbisius.com/products/


Author
------

Svetoslav Marinov (SLAVI)

Site: http://orbisius.com
Twitter: http://facebook.com/orbisius
Facebook: http://twitter.com/orbisius

