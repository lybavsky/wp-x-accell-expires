const DEFAULT_TTL = 60;

const FIELDS_COUNT = 2;

const DEFAULT_RULE = "^\/some_uri\.*";

const DEFAULT_ISREGEX = false;

window.onload = function () {
    field_rules = document.getElementById("x_cache_rules");
    cont = document.getElementById("x_rules_cont");
    submit_btn = document.getElementById("submit");


    submit_btn.onclick = function (e) {
        save_field()

        for (var idx = 0; idx < x_rules.length; idx++) {
            val = x_rules[idx];

            if (val["ttl"] < 0) {
                alert("ttl (" + val["ttl"] + ") не должен быть меньше нуля")
                e.preventDefault()
                return;
            }
        }
    }

    load_field()
    render_field()
}

var x_rules = []

var field_rules = null
var cont = null
var submit_btn = null

function render_field() {
    var rules_html = "";

    x_rules.forEach(function (x_rule, x_rule_idx) {
        var rule_html = "<div style='margin-bottom: 5px'>" +
            "<input id='x_rule_field_rule_" + x_rule_idx + "' type='text' value='" + x_rule["rule"] + "' onchange='save_x_rules()'>" +
            "<input id='x_rule_field_ttl_" + x_rule_idx + "' type='number' value='" + x_rule["ttl"] + "' onchange='save_x_rules()'>" +
            "<input id='x_rule_field_isregex_" + x_rule_idx + "' type='checkbox' " + (x_rule["isregex"] === "true" ? 'checked' : '') + " onchange='save_x_rules()' style='margin-left: 3px'>" +
            "<button onclick='delete_row(" + x_rule_idx + ")'>del</button>"
            + "</div>";
        rules_html += rule_html;
    })

    rules_html += "<div>" +
        "<input type='button' onclick='new_row()' value='add'>"
    "</div>"

    cont.innerHTML = rules_html;
}

//Загружаем значения ttl из сеттинга вордпресса
function load_field() {
    var rule_rows = field_rules.value.split("\n");

    rule_rows.forEach(function (rule_row, rule_row_idx, arr) {
        var col_split_idxs = []

        for (var i = 0; i < rule_row.length; i++) {
            if (rule_row[i] === ";" && (i === 0 || rule_row[i - 1] !== "\\")) {
                col_split_idxs.push(i);
            }
        }
        if (col_split_idxs.length !== FIELDS_COUNT) {
            xmsg("Problem while parse value: " + rule_row);
            return
        }

        var rule = ""
        var ttl = 0
        var isregex = false


        var rule = rule_row.substring(0, col_split_idxs[0]).replaceAll("\\;", ";");
        var ttl = rule_row.substring(col_split_idxs[0] + 1, col_split_idxs[1])
        var isregex = rule_row.substring(col_split_idxs[1] + 1,)

        x_rules.push({"rule": rule, "ttl": ttl, "isregex": isregex});
    })
}

function save_x_rules() {
    for (var idx = 0; idx < x_rules.length; idx++) {
        var val = x_rules[idx];
        rule = document.getElementById("x_rule_field_rule_" + idx).value
        ttl = document.getElementById("x_rule_field_ttl_" + idx).value
        isregex = document.getElementById("x_rule_field_isregex_" + idx).checked
        x_rules[idx] = {"rule": rule, "ttl": ttl, "isregex": isregex}
    }

    save_field()


}

//Сохраняем новые значения из наших полей в сеттинг вордпресса
function save_field() {
    var value = ""
    x_rules.forEach(function (val, idx, arr) {
        value += val["rule"].replaceAll(";", "\\;") + ";" + val["ttl"] + ";" + val["isregex"] + (idx !== arr.length - 1 ? "\n" : "")
    })

    field_rules.value = value;

}

function delete_row(idx) {
    x_rules.splice(idx, 1)
    render_field()
    save_field()
}

function new_row() {
    x_rules.push({"rule": DEFAULT_RULE, "ttl": DEFAULT_TTL, "isregex": DEFAULT_ISREGEX})
    render_field()
    save_field()
}


function xmsg(message) {
    console.log("X-Cache-Plugin: " + message)
}
