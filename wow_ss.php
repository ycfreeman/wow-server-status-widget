<?php

##
## WoW Server Status
## Version 4.1
## Copyright 2008 Nick Schaffner
## http://53x11.com
##
## patched by Freeman Man http://www.ycfreeman.com to work with official JSON feed
##
/* 8 jun 2011 patched to use official JSON feed */

function wow_ss_xml($region = 0, $realm = 0) {
    $region = strtolower($region);
    $realm = str_replace(" ", "%20", $realm);
    return "http://" . $region . ".battle.net/api/wow/realm/status?realm=" . $realm;
}

function wow_ss_global() {

    /*
      ##		Here you can preset all of the default variables for the script.
      ##		These variables can also be set when calling the script.
     */

    $wowss['realm'] = "Lightning's Blade"; // Your full Realm (server) name
    $wowss['display'] = 'text';    // (full | half | text | none) displays full or half image, text or set to none to return an array
    $wowss['region'] = 'us';     // (us | eu) set your server location
    $wowss['update_timer'] = 0;     // Minutes between status update refresh
    $wowss['data_path'] = 'wowss';    // Path to your 'wowss' folder (you may need to prepend this with your root path, 'root/public_html' etc)
    $wowss['image_type'] = 'png';    // (png | gif) image type output

    /*
      ##		These are the default messages outputed by the text version of the script.
     */

    $wowss['up'] = 'Realm Up';   // Set your "Realm Up" message
    $wowss['down'] = 'Realm Down';   // Set your "Realm Down" message
    $wowss['max'] = 'Max (Queued)';  // Set Maxed (Queued)
    $wowss['high'] = 'High';    // Set High Population
    $wowss['medium'] = 'Medium';    // Set Medium Population
    $wowss['low'] = 'Low';    // Set Low Population
    $wowss['pve'] = 'PvE';    // Name for PvE servers
    $wowss['pvp'] = 'PvP';    // Name for PvP servers
    $wowss['rp'] = 'RP';     // Name for RP servers
    $wowss['rppvp'] = 'RPPvP';    // Name for RpPVP servers
    $wowss['offline'] = 'Offline';   // Set Offline Message (for when status is unavailable)
    $wowss['error'] = 'Sever Error';  // Set Error Message

    /*
      ##		Uncomment these variables at YOUR OWN RISK.  These overide default script settings
     */

    ## $wowss['show_language'] 	= 'yes';				// (yes | no) Force script to display language type	(EU realms display language by default)
    ## $wowss['xml_url'] 		= 'http://www.worldofwarcraft.com/realmstatus/status.xml';	// URL to XML status page
    ## $wowss['server_font'] 	= 'silkscreen.ttf'; 	// Font for Server names
    ## $wowss['type_font'] 		= 'silkscreenb.ttf'; 	// Font for all other type
################################
################################ PHP Magic Below, Avoid editing if you don't know what you are doing :)
################################

    $wowss['get_array'] = array('realm', 'update_timer', 'display', 'region', 'data_path', 'image_type');
    foreach ($wowss['get_array'] as $value) {
        if ($_GET[$value])
            $wowss[$value] = trim(stripslashes($_GET[$value]));
    }

    /* 8 jun 2011 patched to use official bnet feed */
    $wowss['xml_url'] = wow_ss_xml($wowss['region'], $wowss['realm']);

    $wowss['bnet_codes'] = array(
        'type' => array('pve' => 'pve', 'pvp' => 'pvp', 'rppve' => 'rp', 'rppvp' => 'rppvp'),
        'status' => array(false => 'down', true => 'up'),
        'population' => array('low' => 'low', 'medium' => 'medium', 'high' => 'high', 'n/a' => 'offline')
    );
    if (substr($wowss['data_path'], -1) != '/')
        $wowss['data_path'] .= '/';

    return $wowss;
}

function wow_ss($realm = 0, $display = 0, $region = 0, $update_timer = 0, $data_path = 0, $image_type = 0) {

    $wowss = wow_ss_global();
    $realm_status = array();
    if ($realm)
        $realm_status['realm'] = $realm;
    else
    /* remove the %20 from display */
        $realm_status['realm'] = str_replace('%20', ' ', $wowss['realm']);;

    ## Overide default values from script call
    foreach ($wowss['get_array'] as $value) {
        if ($$value)
            $wowss[$value] = $$value;
    }

    $realm_status['script_errors'] = array();
    //if (strtolower($wowss['region']) == 'us')
    //$realm_status['language'] = 'us';
    ## Verify data path
    if (is_dir($wowss['data_path'])) {

        //  if (!$wowss['xml_url'])
        //     $wowss['xml_url'] = $wowss[strtolower($wowss['region']) . '_xml'];
        $xml_file = 'wowss-' . wow_ss_sfn($wowss['region']) . '-' . substr(md5($wowss['xml']), 0, 16) . '.xml';
        ## Check if we need to update XML cache
        clearstatcache();
        if (file_exists($wowss['data_path'] . $xml_file)) {
            if (time() - ($wowss['update_timer'] * 60) > filemtime($wowss['data_path'] . $xml_file))
                $update = true;
        }
        else
            $update = true;

        ## Fetch XML
        if ($update) {


            $data = @file_get_contents($wowss['xml_url']);

            if (strlen($data) > 0) {
                ## Don't write data unless it is there
                $handle = fopen($wowss['data_path'] . $xml_file, "w");
                fwrite($handle, $data);
                fclose($handle);
            } else
                $realm_status['script_errors'][] = 'Unable to access remote XML file.';
        }

        $xml = @strtolower(@file_get_contents($wowss['data_path'] . $xml_file));
        ## Parse JSON
       // var_dump($xml);
        if ($xml) {
            /* 8 jun 2011 check if json_decode exists */
            if (!function_exists('json_decode')) {

                function json_decode($content, $assoc=false) {
                    require_once 'JSON.php';
                    if ($assoc) {
                        $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
                    } else {
                        $json = new Services_JSON;
                    }
                    return $json->decode($content);
                }

            }

            if (!function_exists('json_encode')) {

                function json_encode($content) {
                    require_once 'JSON.php';
                    $json = new Services_JSON;
                    return $json->encode($content);
                }

            }
           
            /* 8 jun 2011 official API is json now */
            $status_array = json_decode($xml, true);

            //var_dump($status_array);
            $realm_status['valid'] = $status_array['realms'][0]['name'];
            $realm_status['type'] = $wowss['bnet_codes']['type'][$status_array['realms'][0]['type']];
            $realm_status['status'] = $wowss['bnet_codes']['status'][$status_array['realms'][0]['status']];
            $realm_status['population'] = $wowss['bnet_codes']['population'][$status_array['realms'][0]['population']];
            $realm_status['region'] = $wowss['region'];

           
            if (!$realm_status['valid'])
                $realm_status['status'] = 'error';
            if (!$realm_status['population'])
                $realm_status['population'] = 'error';
            if (!$realm_status['type'])
                $realm_status['type'] = 'error';
            if (!$realm_status['status'])
                $realm_status['population'] = 'offline';
        } else
            $realm_status['script_errors'][] = 'Unable to access XML file.';
    } else
        $realm_status['script_errors'][] = 'Data Path Error.';



    if ($wowss['display'] == 'full') {

        unset($update);
        clearstatcache();
        if (file_exists($wowss['data_path'] . strtolower(wow_ss_sfn($realm_status['realm'] . ' ' . $wowss['region'])) . '.' . $wowss['image_type'])) {
            if (time() - ($wowss['update_timer'] * 60) > filemtime($wowss['data_path'] . strtolower(wow_ss_sfn($realm_status['realm'] . ' ' . $wowss['region'])) . '.' . $wowss['image_type']))
                $update = true;
        }
        else
            $update = true;

        if ($update) {
            ## Write image

            $wowss[$realm_status['type']] .= ' ' . trim(strtoupper($realm_status['region']));
         
            wow_ss_image($realm_status, $wowss);
        }

        if (!headers_sent()) {
            ## Write headers and image if script is called from img tag
            if ($wowss['image_type'] == 'png') {
                header("Content-type: image/png");
                $image = imagecreatefrompng($wowss['data_path'] . strtolower(wow_ss_sfn($realm_status['realm'] . ' ' . $wowss['region'])) . '.' . $wowss['image_type']);
                imagepng($image);
            } elseif ($wowss['image_type'] == 'gif') {
                header("Content-type: image/gif");
                $image = imagecreatefromgif($wowss['data_path'] . strtolower(wow_ss_sfn($realm_status['realm'] . ' ' . $wowss['region'])) . '.' . $wowss['image_type']);
                imagegif($image);
            }
        } else
            echo '<img alt="WoW Server Status for ' . $realm_status['realm'] . '" src="' . $wowss['data_path'] . strtolower(wow_ss_sfn($realm_status['realm'] . ' ' . $wowss['region'])) . '.' . $wowss['image_type'] . '" />';
    }
    if ($wowss['display'] == 'half') {

        if (count($realm_status['script_errors']) or $realm_status['status'] == 'error')
            $realm_status['status'] = 'unknown';
        if (!headers_sent()) {
            ## Write headers and image if script is called from img tag
            if ($wowss['image_type'] == 'png') {
                header("Content-type: image/png");
                $image = imagecreatefrompng($wowss['data_path'] . $realm_status['status'] . '.' . $wowss['image_type']);
                imagepng($image);
            } elseif ($wowss['image_type'] == 'gif') {
                header("Content-type: image/gif");
                $image = imagecreatefromgif($wowss['data_path'] . $realm_status['status'] . '.' . $wowss['image_type']);
                imagegif($image);
            }
        } else
            echo '<img alt="WoW Server Status for ' . $realm_status['realm'] . '" src="' . $wowss['data_path'] . $realm_status['status'] . '.' . $wowss['image_type'] . '" />';
    }
    if ($wowss['display'] == 'text') {

        if ($wowss['show_language'] == 'yes')
            $wowss[$realm_status['type']] .= ' (' . trim(strtoupper($realm_status['language'])) . ')';
        if (strtolower($wowss['region']) == 'eu' and !$realm_status['show_language'])
            $wowss[$realm_status['type']] .= ' (' . trim(strtoupper($realm_status['language'])) . ')';
        if (count($realm_status['script_errors'])) {
            echo '<u>' . $realm_status['realm'] . '</u>';
            foreach ($realm_status['script_errors'] as $value)
                echo ' ' . $value;
        } else {
            if ($realm_status['status'] != 'error')
                echo '<u>' . $realm_status['realm'] . '</u> ' . $wowss[$realm_status['type']] . ': <b>' . $wowss[$realm_status['status']] . ' | ' . $wowss[$realm_status['population']] . '</b>';
            else
                echo '<u>' . $realm_status['realm'] . '</u> Error contacting server.';
        }
    }
    if ($wowss['display'] == 'none') {
        return $realm_status;
    }
}

function wow_ss_image($realm_status, $wowss) {

    ## Error control
    if ($realm_status['status'] == 'down')
        $realm_status['population'] = 'offline';
    if ($realm_status['status'] == 'error' or count($realm_status['script_errors'])) {
        $realm_status['status'] = 'unknown';
        $realm_status['population'] = 'error';
    }
    if ($realm_status['type'] == 'error')
        unset($realm_status['type']);

    ## Set Default Fonts
    if (!$wowss['server_font'])
        $wowss['server_font'] = 'silkscreen.ttf';
    if (!$wowss['type_font'])
        $wowss['type_font'] = 'silkscreenb.ttf';
    $server_font = $wowss['data_path'] . $wowss['server_font'];
    $type_font = $wowss['data_path'] . $wowss['type_font'];

    ## Get and combine base images, set colors
    if ($wowss['image_type'] == 'png')
        $back = imagecreatefrompng($wowss['data_path'] . $realm_status['status'] . '.png');
    if ($wowss['image_type'] == 'gif')
        $back = imagecreatefromgif($wowss['data_path'] . $realm_status['status'] . '.gif');

    $backwidth = imagesx($back);
    if ($wowss['image_type'] == 'png')
        $bottom = imagecreatefrompng($wowss['data_path'] . strtolower($realm_status['status']) . '2.png');
    if ($wowss['image_type'] == 'gif')
        $bottom = imagecreatefromgif($wowss['data_path'] . strtolower($realm_status['status']) . '2.gif');
    if ($wowss['image_type'] == 'png')
        $realm_status['population'] = imagecreatefrompng($wowss['data_path'] . strtolower($realm_status['population']) . '.png');
    if ($wowss['image_type'] == 'gif')
        $realm_status['population'] = imagecreatefromgif($wowss['data_path'] . strtolower($realm_status['population']) . '.gif');
    $full = imagecreate($backwidth, (imagesy($back) + imagesy($bottom)));
    $bg = imagecolorallocate($full, 0, 255, 255);
    $red = imagecolorallocate($full, 204, 0, 0); // HIGH Red color
    imagecolortransparent($full, $bg);
    imagecopy($full, $back, 0, 0, 0, 0, $backwidth, imagesy($back));
    imagecopy($full, $bottom, 0, imagesy($back), 0, 0, imagesx($bottom), imagesy($bottom));
    $back = $full;

    $textcolor = imagecolorallocate($back, 51, 51, 51);
    $shadow = imagecolorclosest($back, 255, 204, 0);

    imagecopy($back, $realm_status['population'], round(($backwidth - imagesx($realm_status['population'])) / 2), 62, 0, 0, imagesx($realm_status['population']), imagesy($realm_status['population']));

    ## Ouput centered $server name
    $maxw = 62;
    $box = imagettfbbox(6, 0, $server_font, $realm_status['realm']);
    $w = abs($box[0]) + abs($box[2]);

    if ($w > $maxw) {

        $i = $w;
        $t = strlen($realm_status['realm']);

        while ($i > $maxw) {
            $t--;
            $box = imagettfbbox(6, 0, $server_font, substr($realm_status['realm'], 0, $t));
            $i = abs($box[0]) + abs($box[2]);
        }

        $t = strrpos(substr($realm_status['realm'], 0, $t), " ");

        $output[0] = substr($realm_status['realm'], 0, $t);
        $output[1] = ltrim(substr($realm_status['realm'], $t));
        $vadj = -6;
    } else
        $output[0] = $realm_status['realm'];

    $i = 0;
    foreach ($output as $value) {
        $box = imagettfbbox(6, 0, $server_font, $value);
        $w = abs($box[0]) + abs($box[2]);

        imagettftext($back, 6, 0, round(($backwidth - $w) / 2) + 1, 58 + ($i * 8) + $vadj, $shadow, $server_font, $value);
        imagettftext($back, 6, 0, round(($backwidth - $w) / 2), 57 + ($i * 8) + $vadj, -$textcolor, $server_font, $value);
        $i++;
    }

    ## Ouput centered $realm_status['type']
    if ($realm_status['type'] and !$err) {
        $realm_status['type'] = $wowss[$realm_status['type']];
        $box = imagettfbbox(6, 0, $type_font, $realm_status['type']);
        $w = abs($box[0]) + abs($box[2]);
        imagettftext($back, 6, 0, round(($backwidth - $w) / 2) + 1, 85, $shadow, $type_font, $realm_status['type']);
        imagettftext($back, 6, 0, round(($backwidth - $w) / 2), 84, -$textcolor, $type_font, $realm_status['type']);
    }

    if ($wowss['image_type'] == 'png')
        imagepng($back, $wowss['data_path'] . strtolower(wow_ss_sfn($realm_status['realm'] . ' ' . $wowss['region'])) . '.png');
    if ($wowss['image_type'] == 'gif')
        imagegif($back, $wowss['data_path'] . strtolower(wow_ss_sfn($realm_status['realm'] . ' ' . $wowss['region'])) . '.gif');
    imagedestroy($back);
}

function wow_ss_sfn($text) {
    ## Returns safe text for inserting into file name
    return str_replace(' ', '_', preg_replace('/[^a-zA-Z0-9- ]/', '', $text));
}

if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME']))
    wow_ss();
?>