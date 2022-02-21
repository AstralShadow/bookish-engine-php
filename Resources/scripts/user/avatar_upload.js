import { ajax } from "../utility/ajax.js"
import { setAvatar } from "./user.js"

const form = document.querySelector("#avatar_form")
const input = form.elements.namedItem("avatar")

function reportFileTooBig()
{
    console.log("File too big")
}

input.addEventListener("input", async function()
{
    const data = new FormData(form)
    var file = data.get("avatar")
    if(!file) return;
    
    if(file.size > 1024 * 1024)
    {
        reportFileTooBig()
        return;
    }

    var result = await
        ajax("POST", "/api/user/avatar", data)

    setAvatar(result.data.uri)
})


