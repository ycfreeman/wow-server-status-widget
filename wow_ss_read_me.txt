## WOW Server Status
## Version 4.1
## Copyright 2008 Nick Schaffner
## http://53x11.com

This lightweight PHP script will parse Blizzard's XML feed and display realm status on your Guild's web page.  The output can be configured as text/HTML, a dynamically generated image or as a PHP array.

During peak hours, Blizzard's website connection is often unreliable.  This script solves that problem by caching the XML data and only checking for updates at definable intervals.

The latest version supports both US and European servers (except Russia, sorry comrades).

This script requires PHP 4.3 or greater and the PHP GD Library installed on your server (very common library).

1. To test if your server meets the requirements, upload the file `server_test.php` to any directory and run it from your browser.  The script will let you know if your server is properly configured.

2. There are a few variables you can set from within the script.  The majority of these variables can also be changed on the fly via `$_GET` or function calls.  If you are changing any variables within the script, make sure to remain within the quotes.

	I won’t explain all of the variables, just a few noteworthy ones.

3. `$display` controls what the script outputs.  `full` will display the larger badge, `half` will display the mini-badge, `text` will output plain text and `none` will return an array of the data within PHP.

4. `$image_type` dictates the type of image the script will output, png (default) or gif.  Some older browsers do not support pngs.

5. Upload `wow_ss.php` and the **wowss** folder to your website.  They don’t need to be in the same directory.

6. The script can be executed in several ways.  The simplest method is to call the script as an image from within any type of HTML page.  Use the following code:

    <img alt="WoW Server Status" src="wow_ss.php" />

	Using this method, you can control the various aspects of the script using `$_GET` without changing having change the hard-coded variables.  `$realm`, `$display` (full | half), `$region` (us | eu), `$update_timer` (in minutes), `$data_path` and `$img_type` (png | gif).

	For example, to output a mini-badge for the Ner'zhul server:

    <img alt=”WoW Server Status” src=”wow_ss.php?realm=Ner’zhul&display=half” />

	Or to output the status for the European Ragnaros server, updating every 5 minutes in gif format:

   <img alt=”WoW Server Status” src=”wow_ss.php?realm=Ragnaros&region=eu&update_timer=5&img_type=gif” />

7. To include the script from within a PHP file, use the `include_once();` and `wow_ss();` to invoke the function.  The `wow_ss();` function takes the same variables as listed above.  Within PHP, the `$display` can also ouput (text | none).

    <?php

        include_once('wow_ss.php');
        wow_ss('Azgalor');

    ?>
	
	Once you `include` the script, you can invoke the `wow_ss();` as many times as you like.  For example, to output the status of Medivh in text format:

    wow_ss('Medivh','text');
	
	To output the status of the European Blackrock server, updating every 15 minutes in png format:

    wow_ss('Blackrock',0,'eu',15,0,'png');
    

    
## Version History

4.1 (08/08/15) - Changed default the Euro XML feed URL
    
4.0 (08/07/31) - Complete script rewrite, added function calls, Euro XML support. Error handling and header detection. Added GIF support

3.4 (06/04/10) - Small XML Change

3.3 (06/01/18) - Blizzard XML Supported - European servers not supported with version 3.3

3.2 (05/06/18) - German and French server status

3.1 (05/04/20) - Added (english) European server support

3.0 (05/04/20) - Added GD image library functions to generate an image based on server status, added caching to speed up script access, slightly improved error handling.

2.1 (05/04/18) - Fixed glaring PHP exit error

2.0 (05/04/18) - Rewrote most of the parser to accommodate Blizzard's changes, added error handling

2.2 (05/04/18) - Bah, Blizzard changes their HTML again, tiny fix

1.0 (04/12/08) - HTML Parser