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

const CACHE_TTL = 3600;
const CACHE_KEY = "x_cache_accel_array";

const SETTINGS_SECTION_ACCEL = "cache_accel";
const SETTINGS_SECTION_DROP = "cache_drop";

class XCache
{

    function __construct()
    {
        add_action('admin_menu', array($this, "show_menu"));
        add_action('admin_init', array($this, "setup_sections"));
        add_action('admin_init', array($this, "setup_fields"));

        add_action('updated_option', array($this, "update_option_callback"));

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
        $rules = get_transient(CACHE_KEY);

        $uri = $_SERVER["REQUEST_URI"];

        if ($rules == false) {
            $rules = $this->save_to_cache();
        }

        foreach ($rules as $rule) {
            $rstr = trim($rule["rule"]);
            $found = false;
            if ($rule['isregex']!="true" && $uri == $rstr) {
                error_log($rstr . " matches " . $uri);
                $found = true;
            } else if ($rule['isregex']=="true") {
                $match = preg_match( "/".$rstr."/", $uri);
                if ($match) {
                    error_log($rstr . " matches regex " . $uri);
                    $found = true;
                }
            }

            if ($found) {
                $headers["X-Accel-Expires"] = $rule["ttl"];
                return $headers;
            }
        }

        return $headers;
    }

    function update_option_callback($opt_name)
    {
        if ($opt_name == OPTION_NAME) {
            $this->save_to_cache();
        }

    }


    static function save_to_cache()
    {
        $rules_option = get_option(OPTION_NAME);
        $rules_str_arr = explode("\r", $rules_option);

        $rules = [];


        for ($r_idx = 0; $r_idx <= sizeof($rules_str_arr); $r_idx++) {
            $rule_str = $rules_str_arr[$r_idx];
            if (trim($rule_str) == "") {
                continue;
            }

            $idxs = [];

            for ($i = 0; $i < strlen($rule_str); $i++) {
                if ($rule_str[$i] === ";" && ($i === 0 || $rule_str[$i - 1] !== "\\")) {
                    array_push($idxs, $i);
                }
            }

            $rule = substr($rule_str, 0, $idxs[0]);
            $ttl = substr($rule_str, $idxs[0] + 1, $idxs[1] - $idxs[0] - 1);
            $isregex = substr($rule_str, $idxs[1] + 1, strlen($rule_str));

            //error_log("rule: " . $rule);
            array_push($rules, ["rule" => $rule, "ttl" => $ttl, "isregex" => $isregex]);
        }

        $cache_set_success = set_transient(CACHE_KEY, $rules, CACHE_TTL);
        if (!$cache_set_success) {
            error_log("add cache no success");
        } else {
            error_log("add to cache successful: " . sizeof($rules));
        }

        return $rules;
    }
}


$xcache = new XCache();