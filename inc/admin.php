<?php
/**
 * Widget Admin Page
 */
add_action('admin_init', 'wow_ss_options_init');
add_action('admin_menu', 'wow_ss_options_add_page');

// Init plugin options to white list our options
function wow_ss_options_init()
{
    register_setting('wow_ss_options_options', PLUGIN_OPTION_KEY, 'wow_ss_options_validate');
}

// Add menu page
function wow_ss_options_add_page()
{
    add_options_page('WOW Server Status Widget Options', 'WOW Server Status Widget', 'manage_options', 'wow_ss_options', 'wow_ss_options_do_page');
}

// Draw the menu page itself
function wow_ss_options_do_page()
{
    ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32">
            <br>
        </div>
        <h2>
            WOW Recruit Widget Options
        </h2>

        <p>
            <a href="<?php echo WSS_BUG_URL; ?>" target="_blank">
                <img style="vertical-align: middle;"
                     src="<?php echo plugins_url( "../images/ic_bug_report.svg", __FILE__ ); ?>"
                     alt="report bugs"/>Report Bugs</a></p>

        <p><em>To use this widget, you need a Battle.net API key, get one at <a href="https://dev.battle.net/" target="_blank">dev.battle.net</a>.</em></p>
        <form method="post" action="options.php">
            <?php settings_fields('wow_ss_options_options'); ?>
            <?php $options = get_option(PLUGIN_OPTION_KEY); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Blizzard Developer API Key</th>
                    <td><input style="width:100%;" type="text" name="<?php echo PLUGIN_OPTION_KEY;?>[api_key]"
                               value="<?php echo $options['api_key']; ?>"/></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" class="button-primary"
                       value="<?php _e('Save Changes') ?>"/>
            </p>
        </form>
    </div>
    <?php
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wow_ss_options_validate($input)
{

    foreach ($input as $k => $v) {
        $input[$k] = wp_filter_nohtml_kses($v);
    }

    return $input;
}
