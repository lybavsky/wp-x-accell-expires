<?php
function set_x_accel_expires($headers)
{
    $rules = get_transient(CACHE_KEY);

    $uri = $_SERVER["REQUEST_URI"];

    if ($rules == false) {
        $rules = save_to_cache();
    }

    foreach ($rules as $rule) {
        $rstr = trim($rule["rule"]);
        $found = false;
        if ($rule['isregex'] != "true" && $uri == $rstr) {
            error_log($rstr . " matches " . $uri);
            $found = true;
        } else if ($rule['isregex'] == "true") {
            $match = preg_match("/" . $rstr . "/", $uri);
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

?>