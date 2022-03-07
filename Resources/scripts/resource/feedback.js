import { ajax } from "../utility/ajax.js"

const container = document.querySelector
                      ("#feedback_container")
const no_comment = document.querySelector("#no_comments")
const other_fb = document.querySelector(".other_feedback")
const your_fb = document.querySelector(".your_feedback")

var comments = []

export async function fetch_data()
{
    const uri = "/api/resource/" + window.id + "/feedback"
    var fb = await ajax("GET", uri)
    comments = fb.data
    render()
}
fetch_data()

function clear()
{
    while(container.firstChild)
        container.removeChild(container.firstChild)
}
clear()

function render()
{
    clear()
    comments.forEach((data) =>
    {
        var element
        if(data.hide_name)
            element = your_fb.cloneNode(1)
        else
            element = other_fb.cloneNode(1)

        var avatar = element.querySelector("user-avatar")
        if(avatar && data.user.avatar)
            avatar.style.backgroundImage = 
                "url(\"" + data.user.avatar + "\")"
        
        var content = element.querySelector(".message")
        if(content)
            content.innerText = data.message

        var name = element.querySelector(".user_name")
        if(name)
            name.innerText = data.user.name

        container.appendChild(element)
    })
    if(comments.length == 0)
        container.appendChild(no_comment)
}
