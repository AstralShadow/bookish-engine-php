import { ajax } from "../utility/ajax.js"

const name_input = document.querySelector("input")
var users = []
var search = ""


async function fetch_users()
{
    var req = await ajax("GET", "/api/admin/users");
    if(req.status == 200)
        users = req.data
}
fetch_users()

name_input.addEventListener("input", function()
{
    search = this.innerText
    update_content()
})

function update_content()
{
    user.forEach(function(user)
    {
        if(search != "")
        {
            if(user.name.indexOf(search) == -1)
                return;
        }
    })
}
