
FCK Editor from http://fckeditor.wikiwikiweb.de/

Changes by Steve Drew for integration...

Sep 2005/

- Removed unused folders such as asp files
- Modified Commans/*.php files to remove /$Type/ from file path, meaning all files store in same root folder.
- copied mpuk file browser to custom fold and modified to support "ServerPath" variable that is passed
  at each invocation to provide a unique path per item (ie KB article)
- Modified common.js to support ServerPath variable
- Modified config.php and filemanager/upload/php/config.php to read from top level config.php which
  contains defines for APP_ROOT_DIR and APP_NAME.  APP_ROOT_DIR is physical path to applications doc root
  and APP_NAME is virtual name, ie "/KB". 
- Added javascript to image diaglog to alert and exit if AllowUploads is not set. Allows server code to setup this
  variable for times that uploads are not permitted.
  
  - Summary of fckeditor kit, Files Modified:
  
  - fckconfig.js
  - fckstyles.xml
  - fcktemplates.xml
  - editor\fckeditor.html (minor style change)
  
  - editor\dialog\fck_image.html (check for article saved/new)
  - editor\dialog\fck_template\images (template preview images)
  
  - editor\css\fck_editorarea.css
  
  - editor\lib\filemanager\mpuk\php\connector.php (in folder custom..., added $noauth)
  - editor\lib\filemanager\mpuk\php\config.php (changes for ServerPath support)
  - editor\filemanager\upload\php\config.php (changes for ServerPath support)
  - editor\filemanager\upload\php\upload.php (changes for ServerPath support)
  
  - added images/smileys/custom (and custom icons)
  
   
  
