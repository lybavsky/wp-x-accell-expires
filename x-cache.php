<?php
/*
Plugin Name: X-Accel-Cache
Plugin URI: https://ash.lt
Description: Plugin to change nginx cache ttl via setting X-Accel-Expires header
Version: 1.0
Author: Artem Shiryaev
Author URI: https://ash.lt
License: GPLv2
Text Domain: x-cache
*/

const PAGE_TITLE = "X-Accel-Cache plugin";
const MENU_TITLE = "X-Cache";
const CAP = "manage_options";
const SLUG = "xcache";
const SETTINGS_NAME = "xcache_settings";

const SETTINGS_SECTION_ACCEL = "cache_accel";
const SETTINGS_SECTION_DROP = "cache_drop";

class XCache
{

    function __construct()
    {
        add_action('admin_menu', array($this, "show_menu"));
        add_action('admin_init', array($this, "setup_sections"));
        add_action('admin_init', array($this, "setup_fields"));

    }

    function show_menu()
    {
        add_submenu_page("options-general.php", PAGE_TITLE, MENU_TITLE, CAP, SLUG, array($this, "page_callback"));
        //add_menu_page(PAGE_TITLE, MENU_TITLE, CAP, SLUG, array($this, "page_callback"));
    }

    function page_callback($attributes)
    {
        ?>
        <script lang="js" src="<?= plugin_dir_url(__FILE__) . "x-cache.js" ?>"></script>

        <h1>
            <?= get_admin_page_title() ?>
        </h1>

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
        echo "<hr/>";
    }

    function setup_fields()
    {
        add_settings_field("testfield", "Test text field", array($this, "setup_field_testfield"), SLUG, SETTINGS_SECTION_ACCEL);
        register_setting(SETTINGS_NAME, "testfield");

        add_settings_field("testfield_js", "Test js field", array($this, "setup_field_jsfield"), SLUG, SETTINGS_SECTION_ACCEL,array("id"=>"testfield_js"));
        register_setting(SETTINGS_NAME, "testfield_js");

    }

    function setup_field_testfield($arguments)
    {
        echo "<input type='text' name='testfield' id='testfield' placeholder='some text' value='" . get_option("testfield") . "'/>";
    }

    function setup_field_jsfield($arguments)
    {
        echo "<input type='text' name='" . $arguments['id'] . "' id='" . $arguments['id'] . "' placeholder='some text' value='" . get_option($arguments['id']) . "'/>";
    }
}

$xcache = new XCache();