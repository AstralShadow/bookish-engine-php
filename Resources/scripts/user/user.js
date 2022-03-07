import { ajax } from "../utility/ajax.js"

const avatar = document.querySelector("user-avatar")

export function setAvatar(uri)
{
    avatar.style.backgroundImage = "url(\"" + uri + "\")"
    if(!uri)
        avatar.style.backgroundImage = ""

    var extra = document.querySelectorAll(".my_avatar")
    extra.forEach(avatar => {
        avatar.style.backgroundImage = 'url("'+ uri +'")'
        if(!uri)
            avatar.style.backgroundImage = ""
    })
}

(async function()
{
    const request = await ajax("GET", "/api/user");
    const data = request.data

    if(!data) return;

    setAvatar(data.avatar)

})()
