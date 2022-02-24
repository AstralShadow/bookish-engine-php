import { ajax } from "../utility/ajax.js"

const buy_btns = document.querySelectorAll(".buy_item")
const feedbacks = Array.from(document.querySelectorAll
    (".buy_error"))
const download_btns = Array.from(document.querySelectorAll
    (".download_item"))

var tuples = []

buy_btns.forEach(function(buy_btn)
{
    const item_id = buy_btn.getAttribute("data-item-id")
    const feedback = feedbacks.find((fb) => {
        return fb.getAttribute("data-item-id") == item_id
    })
    const download_btn = download_btns.find((btn) => {
        return btn.getAttribute("data-item-id") == item_id
    })

    tuples.push({
        id: item_id,
        buy_btn: buy_btn,
        feedback: feedback,
        download_btn: download_btn
    })
})

tuples.forEach(function(tuple)
{
    const item_id = tuple.id
    const buy_btn = tuple.buy_btn
    const feedback = tuple.feedback
    const download_btn = tuple.download_btn

    update_state(item_id)
    buy_btn.addEventListener("click", async function()
    {
        var data = new FormData()
        var uri = "/api/resources/" + item_id + "/buy"
        data.append("csrf", window.csrf)

        var result = await ajax("POST", uri, data)
    })

})

async function update_state(item_id)
{
    const tuple = tuples.find(e => e.id == item_id)
    const buy_btn = tuple.buy_btn
    const download_btn = tuple.download_btn

    buy_btn.style.display = "none"
    download_btn.style.display = "none"
    var state = await fetch_state(item_id)

    if(state.accured)
        download_btn.style.display = ""
    else
        buy_btn.style.display = ""
}

async function fetch_state(id)
{
    var req = await ajax("GET", "/api/resource/" + id)

    return req?.data
}
