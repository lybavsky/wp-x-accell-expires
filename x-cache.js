function x_cache_init(){
    var el=document.getElementById("testfield_js");
    el.value="ttt"+el.value
}

window.onload = function (){
    x_cache_init()
}