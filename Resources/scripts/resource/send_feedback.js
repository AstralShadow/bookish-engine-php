import { ajax } from "../utility/ajax.js"
import { fetch_data } from "./feedback.js"

const input = document.querySelector("#user_comment")

const form = document.querySelector("#feedback_form")

form.addEventListener("submit", function(e)
{
    attempt_submit()
    e.preventDefault()
    return false
})

async function attempt_submit()
{
    if(!form.reportValidity()) return;
    
    var data = new FormData()
    data.append("csrf", window.csrf)
    data.append("text", input.value)

    const endpoint = "/api/resource/" + window.id + "/feedback"
    var req = await ajax("POST", endpoint, data)

    const status_bar = document.querySelector
        ("#feedback_form_feedback")
    if(req.data?.error)
        status_bar.innerText = req.data.error
    fetch_data()
}
