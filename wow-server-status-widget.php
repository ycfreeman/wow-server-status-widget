<?php
/**
 * Plugin Name: WOW Server Status Widget
 * Plugin URI: http://www.ycfreeman.com/category/wow-server-status-widget
 * Description: Add WOW Server Status 4.1 badge to your site by a few clicks.
 * Version: 1.0.7
 * Author: Freeman Man
 * Author URI: http://www.ycfreeman.com
 */
/**
 * Copyright (C) 2010  Freeman, (Yu Chung) Man (email : ycfreeman@yahoo.com.hk)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/.
 */
/**
 * help and bug report url as well as icons url for easier maintainence
 */

define("WSS_BUG_URL", "http://www.ycfreeman.com/forum/support-wss");
define("YCFREEMAN_BUG_ICON_URL","http://img835.imageshack.us/img835/4069/bugicon.png");

/**
 * bulletproof plugin path handling
 * @since 1.0.1
 */
define("WSS_PATH", WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)));

/**
 * Add function to widgets_init that'll load our widget.
 * @since 1.0
 */
add_action('widgets_init', 'wow_ss_load_widgets');

/**
 * Register our widget.
 * 
 * @since 1.0
 */
function wow_ss_load_widgets() {
    register_widget('Wow_SS_Widget');
}

/**
 * WOW Server Status Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.
 *
 * @since 1.0
 */
class Wow_SS_Widget extends WP_Widget {

    /**
     * Widget setup.
     */
    function Wow_SS_Widget() {
        /* Widget settings. */
        $widget_ops = array('classname' => 'wow-ss-widget', 'description' => __('Displays specific WOW realm status.', 'wow-ss-widget'));

        /* Widget control settings. */
        $control_ops = array('width' => 300, 'height' => 600, 'id_base' => 'wow-ss-widget');

        /* Create the widget. */
        $this->WP_Widget('wow-ss-widget', __('WOW Server Status Widget', 'wow-ss-widget'), $widget_ops, $control_ops);
    }

    /**
     * How to display the widget on the screen.
     */
    function widget($args, $instance) {

        extract($args);

        /* Our variables from the widget settings. */
        $title = apply_filters('widget_title', $instance['title']);



        /* Before widget (defined by themes). */
        echo $before_widget;

        /* Display the widget title if one was input (before and after defined by themes). */
        if ($title) {
            echo $before_title . $title . $after_title;
        }



        /**
         * Frontend Start
         */
?>

        <div style="text-align: <?php echo $instance['align'] ?>">
            <img alt="<?php echo $instance['realm']; ?> realm status"
                 src="<?php
        echo
        WSS_PATH .
        'wow_ss.php?' .
        'realm=' . $instance['realm'] .
        '&region=' . $instance['region'] .
        '&display=' . $instance['display'] .
        '&img_type=' . $instance['img_type']; ?>" />
</div>

<?php
        /**
         *  Frontend End
         */
        /* After widget (defined by themes). */
        echo $after_widget;
    }

    /**
     * Update the widget settings.
     */
    function update($new_instance, $old_instance) {

        $instance = $old_instance;

        /* Strip tags for title and title_url to remove HTML (important for text inputs). */
        $instance['title'] = strip_tags($new_instance['title']);

        //updating $instance
        $instance['realm'] = strip_tags($new_instance['realm']);
        $instance['region'] = strip_tags($new_instance['region']);

        $instance['align'] = strip_tags($new_instance['align']);
        $instance['display'] = strip_tags($new_instance['display']);
        $instance['img_type'] = strip_tags($new_instance['img_type']);

        return $instance;
    }

    /**
     * Displays the widget settings controls on the widget panel.
     * Make use of the get_field_id() and get_field_name() function
     * when creating your form elements. This handles the confusing stuff.
     */
    function form($instance) {

        //set defaults
        $defaults = array('realm' => 'Thaurissan',
            'region' => 'us',
            'display' => 'full',
            'img_type' => 'png',
            'align' => 'center'
        );
        $instance = wp_parse_args((array) $instance, $defaults);
?>
        <div style="float:right;">
            <a href="<?php echo WSS_BUG_URL; ?>" target="_blank" >
                <img src="<?php echo YCFREEMAN_BUG_ICON_URL; ?>" title="report bugs" alt="report bugs"/>
            </a>
        </div>
        <!-- Widget Title: Text Input -->
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title(optional):</label>
            <input type="text"
                   id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   value="<?php echo $instance['title']; ?>"
                   style="width:100%;" />
        </p>
        <!-- Realm and Region -->

        <p>
            <label for="<?php echo $this->get_field_id('realm'); ?>"
                   >Realm:</label>
            <br />
            <input type="text"
                   id="<?php echo $this->get_field_id('realm'); ?>"
                   name="<?php echo $this->get_field_name('realm'); ?>"
                   value="<?php echo $instance['realm']; ?>"
                   style="width:70%;" />

            <select id="<?php echo $this->get_field_id('region'); ?>"
                    name="<?php echo $this->get_field_name('region'); ?>"
                    style="width:25%;"
                    >
                <option <?php if ('us' == $instance['region'])
            echo 'selected="selected"'; ?>
            value="us">
            US
        </option>
        <option <?php if ('eu' == $instance['region'])
            echo 'selected="selected"'; ?>
            value="eu">
            EU
        </option>
    </select>
</p>
<!-- wow_ss.php variables -->
<table style="width:100%;">
    <tbody>
        <tr>
            <td style="width:50%;">
                Align
            </td>
            <td>
                <select id="<?php echo $this->get_field_id('align'); ?>"
                        name="<?php echo $this->get_field_name('align'); ?>"

                        >
                    <option <?php if ('left' == $instance['align'])
            echo 'selected="selected"'; ?>
                        value="left">
                        Left
                    </option>
                    <option <?php if ('center' == $instance['align'])
                        echo 'selected="selected"'; ?>
                        value="center">
                        Center
                    </option>
                    <option <?php if ('right' == $instance['display'])
                        echo 'selected="selected"'; ?>
                        value="right">
                        Right
                    </option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                Display
            </td>
            <td>
                <select id="<?php echo $this->get_field_id('display'); ?>"
                        name="<?php echo $this->get_field_name('display'); ?>"
                        >
                    <option <?php if ('full' == $instance['display'])
                        echo 'selected="selected"'; ?>
                        value="full">
                        Full Badge
                    </option>
                    <option <?php if ('half' == $instance['display'])
                        echo 'selected="selected"'; ?>
                        value="half">
                        Half Badge
                    </option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                Image Type
            </td>
            <td>
                <select id="<?php echo $this->get_field_id('img_type'); ?>"
                        name="<?php echo $this->get_field_name('img_type'); ?>"
                        >
                    <option <?php if ('png' == $instance['img_type'])
                        echo 'selected="selected"'; ?>
                        value="png">
                        PNG
                    </option>
                    <option <?php if ('gif' == $instance['img_type'])
                        echo 'selected="selected"'; ?>
                        value="gif">
                        GIF
                    </option>
                </select>
            </td>
        </tr>

    </tbody>
</table>
<?php
                }

            }
?>