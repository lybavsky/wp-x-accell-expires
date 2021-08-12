<?php
/*
Plugin Name: X-Accel-Cache
Plugin URI: https://github.com/lybavsky/wp-x-accell-expires
Description: Plugin to change nginx cache ttl via setting X-Accel-Expires header
Version: 1.0
Author: Artem Shiryaev
Author URI: https://ash.lt
License: GPLv2
Text Domain: x-cache
*/

include "consts.php";
include "cached_settings.php";
include "set_header.php";

class XCache
{

    function __construct()
    {
        add_action('admin_menu', array($this, "show_menu"));
        add_action('admin_init', array($this, "setup_sections"));
        add_action('admin_init', array($this, "setup_fields"));

        add_action('updated_option', "update_option_callback");

        add_filter('wp_headers', "set_x_accel_expires");

    }


    function show_menu()
    {
        add_submenu_page("options-general.php", PAGE_TITLE_SETTINGS, MENU_TITLE_SETTINGS, CAP_SETTINGS, SLUG_SETTINGS, array($this, "page_settings_callback"));

        add_menu_page(PAGE_TITLE, MENU_TITLE, CAP, SLUG, array($this, "page_callback"));
    }

    function page_callback($attributes)
    {
        ?>
        <h1>PAGE</h1>


        <div>
            <hr/>
            <form action="<?=plugin_dir_url(__FILE__)?>x-cache-drop.php" method="POST" target="_blank">
                <input type="text" name="url" style="min-width: 500px" placeholder="full url" id="cache_url_field">
                <input type="submit" value="clear">
            </form>
        </div>
        <?php
    }

    function page_settings_callback($attributes)
    {
        ?>

        <script lang="js" src="<?= plugin_dir_url(__FILE__) . "x-cache.js" ?>"></script>


        <!--        <h1>-->
        <!--            --><?//= get_admin_page_title()
        ?>
        <!--        </h1>-->

        <div>
            <form method="post" action="options.php">
                <?php
                settings_fields(SETTINGS_NAME);
                do_settings_sections(SLUG);

                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    function setup_sections()
    {
        add_settings_section(SETTINGS_SECTION_ACCEL, "X-Accel-Cache settings", array($this, "setup_section_accel"), SLUG);
        add_settings_section(SETTINGS_SECTION_DROP, "Drop cache settings", array($this, "setup_section_drop"), SLUG);
    }


    public function setup_section_accel()
    {
        echo "<hr/>";
    }


    public function setup_section_drop()
    {
        ?>
        <?php
    }

    function setup_fields()
    {

        add_settings_field(OPTION_RULES_NAME, "Cache rules", array($this, "setup_field_x_cache_rules"), SLUG, SETTINGS_SECTION_ACCEL);
        register_setting(SETTINGS_NAME, OPTION_RULES_NAME);

        add_settings_field(OPTION_DROP_URL_NAME, "Jenkins job URL", array($this, "setup_field_drop_url"), SLUG, SETTINGS_SECTION_DROP);
        register_setting(SETTINGS_NAME, OPTION_DROP_URL_NAME);

        add_settings_field(OPTION_DROP_TOKEN_NAME, "Jenkins username:token", array($this, "setup_field_drop_token"), SLUG, SETTINGS_SECTION_DROP);
        register_setting(SETTINGS_NAME, OPTION_DROP_TOKEN_NAME);


    }

    function setup_field_drop_url()
    {
        ?>
        <input type="text" name="<?= OPTION_DROP_URL_NAME ?>"
               placeholder="https://ci.oursite.ru/job/invalidate/buildWithParameters"
               value="<?= trim(get_option(OPTION_DROP_URL_NAME)) ?>">
        <?php
    }

    function setup_field_drop_token()
    {
        ?>
        <input type="text" name="<?= OPTION_DROP_TOKEN_NAME ?>" placeholder="admin:b7ca423fabca0123"
               value="<?= trim(get_option(OPTION_DROP_TOKEN_NAME)) ?>">
        <?php
    }

    function setup_field_x_cache_rules($arguments)
    {
        ?>
        <textarea style="display: none" type='text' name='<?= OPTION_RULES_NAME ?>'
                  id='<?= OPTION_RULES_NAME ?>'><?= trim(get_option(OPTION_RULES_NAME)) ?></textarea>

        <div id='x_rules_cont'>Cont</div>
        <div style="font-size: smaller">
            List of regex-based or full-uri rules to add X-Accell-Expires header with.
            <br/>
            Fields:
            <br/>
            URL: full uri like /posts/414324-some-post or regex like ^\/posts\/[0-9]*-.*$
            <br/>
            TTL: value returned with X-Accel-Expires header (cache ttl in seconds)
            <br/>
            IsRegex: checkbox to set URL field type - full-uri or regex

        </div>
        <?php
    }


}


$xcache = new XCache();