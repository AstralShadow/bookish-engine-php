import { ajax } from "../utility/ajax.js"

const tagarea = document.querySelector("tag-area")
const tag_list = document.querySelector("tag-select")
const form_id = tagarea.getAttribute("form")

const new_tag = document.createElement("tag-option")
const new_name = document.createElement("tag-name")
const new_desc = document.createElement("tag-info")
new_tag.classList.add("selected")
// new_desc.innerText = "Създайте нов елемент"
new_tag.appendChild(new_name)
new_tag.appendChild(new_desc)

const input = document.createElement("span")
input.contentEditable = true

var tags = undefined
var target_tag = -1
var added_tags = []


var shown = false
tagarea.addEventListener("click", async function()
{
    tagarea.appendChild(input)
    input.focus()

    shown = true
    await load_tags()
    if(shown)
    {
        tag_list.classList.add("shown")
        fix_tag_list_design()
    }
})

input.addEventListener("blur", function()
{
    shown = false
    tag_list.classList.remove("shown")
})


async function load_tags(cache = true)
{
    if(!tags || !cache)
    {
        var req = await ajax("GET", "/api/search/tags")
        if(!req.data)
        {
            console.log("Failed loading flags")
            return;
        }
        tags = req.data
        tags.sort((a, b) => a.name > b.name)
    }

    while(tag_list.firstChild)
        tag_list.removeChild(tag_list.firstChild)
    
    tag_list.appendChild(new_tag)

    tags.forEach(function(tag)
    {
        var option = document.createElement("tag-option")
        Object.keys(tag).forEach(function(key)
        {
            if(key == "element")
                return;
            var item = document.createElement("tag-"+key)
            item.innerText = tag[key]
            option.appendChild(item)
        })
        tag_list.appendChild(option)

        tag.element = option
    })
}


input.addEventListener("input", function(e)
{
    var text = input.innerText
    if(text.indexOf("\n") > 0)
    {
        e.preventDefault()
        insertTag()
        this.innerText = ""
    }

    showClosest(text)
})

tagarea.addEventListener("keydown", function(e)
{
    const UP = 38, DOWN= 40
    const BACKSPACE = 8


    if(e.keyCode == UP)
    {
        if(target_tag >= 0)
            set_target(target_tag - 1)
    }
    if(e.keyCode == DOWN)
    {
        if(target_tag < tags.length - 1)
            set_target(target_tag + 1)
    }
    if(e.keyCode == BACKSPACE && input.innerText == "")
    {
        tagarea.removeChild(tagarea.lastChild)
        if(tagarea.lastChild)
        {
            tagarea.removeChild(tagarea.lastChild)
            added_tags.pop()
        }
        tagarea.appendChild(input)
        input.focus()
    }
})

async function insertTag()
{
    if(target_tag == -1)
    {
        var text = input.innerText
        var tag = text.split("\n")[0]
        console.log("create", tag)
        await create_tag(tag)
        await load_tags(true)

        set_target(0)
        while(tags[target_tag].name != tag)
            set_target(target_tag + 1)
    }

    var tag = tags[target_tag]
    if(added_tags.indexOf(tag.name) >= 0)
        return;
    added_tags.push(tag.name);

    var element = document.createElement("tag-box")
    element.innerText = tag.name
    tagarea.appendChild(element)
    tagarea.appendChild(input)
    input.focus()

    var form_el = document.createElement("index")
    form_el.setAttribute("form", form_id)
    form_el.setAttribute("type", "hidden")
    form_el.setAttribute("name", "tags[]")
    element.appendChild(form_el)

}

function create_tag(tag)
{
    var data = new FormData()
    data.append("csrf", window.csrf)
    data.append("name", tag)

    return ajax("POST", "/api/search/tags/create", data)
}

function showClosest(tag)
{
    fix_tag_list_design()
    new_tag.style.display = "none"
    new_name.innerText = "[+] " + tag
    
    if(tag == "")
    {
        for(let i = 0; i < tags.length; i++)
            tags[i].element.style.display = "none"
        set_target(-1)
        return;
    }

    var exact_match = false
    for(let i = 0; i < tags.length; i++)
    {
        var current = tags[i].name
        var index = current.indexOf(tag)
        if(index < 0)
        {
            tags[i].element.style.display = "none"
            continue
        }
        tags[i].element.style.display = ""
        
        if(current == tag)
        {
            set_target(i)
            exact_match = true
        }
    }
    if(!exact_match)
    {
        new_tag.style.display = ""
        set_target(-1)
    }
}

function fix_tag_list_design()
{
    tag_list.style.minWidth = tagarea.offsetWidth + "px"
    tag_list.parentElement.style.left =
        tagarea.offsetLeft + "px"
}

function set_target(index)
{
    (target_tag < 0 ? new_tag : tags[target_tag].element)
        .classList.remove("selected")

    target_tag = index;
    if(target_tag > tags.length)
        target_tag = -1

    (target_tag < 0 ? new_tag : tags[target_tag].element)
        .classList.add("selected")
}
