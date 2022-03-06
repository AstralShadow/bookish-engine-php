import { ajax } from "../utility/ajax.js"

const name_input = document.querySelector("input")
var users = []
var search = ""


async function fetch_users()
{
    var req = await ajax("GET", "/api/admin/users");
    if(req.status == 200)
        users = req.data
    update_content()
}
fetch_users()

name_input.addEventListener("input", function()
{
    search = this.innerText
    update_content()
})


const noobs = document.querySelector("#noobs_list")
const noobs_sample = noobs.querySelector("h-flex")
const mods = document.querySelector("#mods_list")
const mods_sample = mods.querySelector("h-flex")

function update_content()
{
    while(noobs.firstChild)
        noobs.removeChild(noobs.firstChild)
    while(mods.firstChild)
        mods.removeChild(mods.firstChild)

    users.forEach(function(user)
    {
        if(search != "")
        {
            if(user.name.indexOf(search) == -1)
                return;
        }
        if(!user.element)
            create_user_element(user)

        if(user.role == "Потребител")
            noobs.appendChild(user.element)
        if(user.role == "Модератор")
            mods.appendChild(user.element)
    })
}

function create_user_element(user)
{
    var element = null

    if(user.role == "Потребител")
        element = noobs_sample.cloneNode(true)
    else if(user.role == "Модератор")
        element = mods_sample.cloneNode(true)

    if(!element) return;
    
    var avatar = element.querySelector("user-avatar")
    if(user.avatar)
        avatar.style.backgroundImage =
            'url("' + user.avatar + '")'

    var name = element.querySelector("span")
    name.innerText = user.name + " - " + user.role

    var button = element.querySelector("button")
    button.addEventListener("click", () => action(user))
    if(user.role == "Потребител")
        button.innerText = "Повиши"
    if(user.role == "Модератор")
        button.innerText = "Премахни"
    
    user.element = element
}

async function action(user)
{
    var data = new FormData()
    data.append("csrf", window.csrf)
    data.append("user", user.name)
    if(user.role == "Модератор")
        await ajax("POST", "/api/admin/take_mod", data)
    if(user.role == "Потребител")
        await ajax("POST", "/api/admin/give_mod", data)

    fetch_users()
}
