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

const OPTION_NAME = "x_cache_rules";

const CACHE_TTL = 60;

const SETTINGS_SECTION_ACCEL = "cache_accel";
const SETTINGS_SECTION_DROP = "cache_drop";

class XCache
{

    function __construct()
    {
        add_action('admin_menu', array($this, "show_menu"));
        add_action('admin_init', array($this, "setup_sections"));
        add_action('admin_init', array($this, "setup_fields"));


        add_filter('wp_headers', array($this, "set_x_accel_expires"));

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

        add_settings_field("x_cache_rules", "Cache rules", array($this, "setup_field_x_cache_rules"), SLUG, SETTINGS_SECTION_ACCEL);
        register_setting(SETTINGS_NAME, OPTION_NAME);

    }

    function setup_field_x_cache_rules($arguments)
    {
        ?>
        <textarea style="display: none" type='text' name='<?= OPTION_NAME ?>'
                  id='<?= OPTION_NAME ?>'><?= trim(get_option(OPTION_NAME)) ?></textarea>

        <div id='x_rules_cont'>Cont</div>
        <?php
    }


    function set_x_accel_expires($headers)
    {
        $crules = wp_cache_get("x_cache_accel_array");

        if ($crules == false) {
            $crules = save_to_cache();
        }

        return $headers;
    }
}

function save_to_cache()
{
    $frules = get_option(OPTION_NAME);
    $srules = explode("\r", $frules);


    for ($r_idx = 0; $r_idx <= sizeof($srules); $r_idx++) {
        $srule = $srules[$r_idx];
        $idxs = [];

        for ($i = 0; $i < strlen($srule); $i++) {
            if ($srule[$i] === ";" && ($i === 0 || $srule[$i - 1] !== "\\")) {
                array_push($idxs, $i);
            }
        }

        $rule = substr($srule, 0, $idxs[0]);
        $ttl = substr($srule, $idxs[0] + 1, $idxs[1] - $idxs[0] - 1);
        $isregex = substr($srule, $idxs[1] + 1, strlen($srule));

        error_log("rule: " . $srule . " -> " . $rule . " -> " . $ttl . " -> " . $isregex);
    }

}


$xcache = new XCache();