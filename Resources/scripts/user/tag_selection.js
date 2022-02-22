import { ajax } from "../utility/ajax.js"

const tagarea = document.querySelector("tag-area")
const tag_list = document.querySelector("tag-select")
const form_id = tagarea.getAttribute("form")

const new_tag = document.createElement("tag-option")
const new_name = document.createElement("tag-name")
const new_desc = document.createElement("tag-info")
new_desc.innerText = "Създайте нов елемент"
new_tag.appendChild(new_name)
new_tag.appendChild(new_desc)

const input = document.createElement("span")
input.contentEditable = true

var tags = undefined
var target_tag = 0 // 0 - new


var shown = false
tagarea.addEventListener("click", async function()
{
    tagarea.appendChild(input)
    input.focus()

    shown = true
    await load_tags()
    if(shown)
        tag_list.classList.add("shown")
})

input.addEventListener("blur", function()
{
    shown = false
    tag_list.classList.remove("shown")
})


async function load_tags(cache = true)
{
    if(!tags || !cache)
        tags = await ajax("GET", "/api/search/tags")
    if(!tags.data)
    {
        console.log("Failed loading flags")
        return;
    }

    while(tag_list.firstChild)
        tag_list.removeChild(tag_list.firstChild)
    
    tag_list.appendChild(new_tag)

    tags.data.forEach(function(tag)
    {
        var option = document.createElement("tag-option")
        Object.keys(tag).forEach(function(key)
        {
            var item = document.createElement("tag-"+key)
            item.innerText = tag[key]
            option.appendChild(item)
        })
        tag_list.appendChild(option)
    })
}


input.addEventListener("input", function()
{
    var text = input.innerText
    if(text.indexOf("\n") != -1)
        insertTag();

    new_name.innerText = text
    console.log(text)
})

tagarea.addEventListener("keydown", function(e)
{
    if(e.keyCode == 38) // up
    if(e.keyCode == 40) // down
    ;
})

async function insertTag()
{
    if(target_tag == 0)
    {
        var text = input.innerText
        var tag = text.split("\n")[0]
        input.innerText = text.split("\n")[1]
        console.log(text)
        await createTag(tag)
        await load_tags(true)
        while(tags[target_tag].name != tag)
            target_tag++
    }

    var tag = tags[target_tag - 1];
    console.log(tag)
}

function createTag(tag)
{
    var data = new FormData()
    data.append("csrf", window.csrf)
    data.append("name", tag)

    return ajax("POST", "/api/search/tags/create", data);
}
