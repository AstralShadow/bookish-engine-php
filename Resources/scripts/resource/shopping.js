import { ajax } from "../utility/ajax.js"

var buttons = document.querySelectors(".buy")
buttons.forEach(async function(btn)
{
    var item_id = btn.getAttribute("data-item-id")

    var data = new FormData()
    var uri = "/api/resources/" + item_id + "/buy"
    data.append("csrf", window.csrf)

    var result = await ajax("POST", uri, data)

})
