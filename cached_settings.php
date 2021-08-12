<?php

function update_option_callback($opt_name)
{
    if ($opt_name == OPTION_RULES_NAME) {
        save_to_cache();
    }
}


function save_to_cache()
{
    $rules_option = get_option(OPTION_RULES_NAME);
    $rules_str_arr = explode("\r", $rules_option);

    $rules = [];


    for ($r_idx = 0; $r_idx < sizeof($rules_str_arr); $r_idx++) {
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

?>