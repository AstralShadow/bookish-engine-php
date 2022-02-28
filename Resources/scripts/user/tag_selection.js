import { ajax } from "../utility/ajax.js"

const tagarea = document.querySelector("tag-area")
const tag_list = document.querySelector("tag-select")
const form_id = tagarea.getAttribute("form") ?? null

const new_tag = document.createElement("tag-option")
const new_name = document.createElement("tag-name")
const new_desc = document.createElement("tag-info")
const enable_creating = window.csrf != undefined

new_tag.classList.add("selected")
// new_desc.innerText = "Създайте нов елемент"
new_tag.appendChild(new_name)
new_tag.appendChild(new_desc)

const input = document.createElement("span")
input.contentEditable = true
input.tabIndex = 0
tagarea.appendChild(input)
const forbidden = [',', '+', ';', '.', '/', "\\"]

var tags = []
var target_tag = -1
var visible_tags = []
var added_tags = []


var shown = false
tagarea.addEventListener("click", function()
{
    tagarea.appendChild(input)
    input.focus()
})

input.addEventListener("focus", async function()
{
    tagarea.classList.add("focus")
    shown = true
    await load_tags()
    if(shown)
    {
        tag_list.classList.remove("hidden")
        fix_tag_list_design()
        show_closest("")
    }
})

input.addEventListener("blur", function()
{
    shown = false
    tag_list.classList.add("hidden")
    input.innerText = ""
    tagarea.classList.remove("focus")
})


async function load_tags(cache = true)
{
    if(!tags.length || !cache)
    {
        var req = await ajax("GET", "/api/tags")
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
    
    if(enable_creating )
        tag_list.appendChild(new_tag)

    tags.forEach(function(tag, tag_id)
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
        
        option.addEventListener("mousemove", function(){
            set_target(tag_id)
        })
        option.addEventListener("mousedown", function(){
            input.innerText = tag.name 
            insert_tag()
            input.innerText = ""
        })

        tag.element = option
    })
}


input.addEventListener("input", function(e)
{
    forbidden.forEach(function(sym)
    {
        while(input.innerText.indexOf(sym) != -1)
            input.innerText =
                input.innerText.replace(sym, "")
    })
    
    show_closest(this.innerText)
})

input.addEventListener("keydown", function(e)
{
    forbidden.forEach(function(sym)
    {
        while(input.innerText.indexOf(sym) != -1)
            input.innerText =
                input.innerText.replace(sym, "")
    })

    if(e.keyCode == 13)
    {
        e.preventDefault()
        insert_tag()
        this.innerText = ""
    }
    show_closest(this.innerText)
})

new_tag.addEventListener("mousedown", function()
{
    insert_tag()
    this.innerText = ""
})

tagarea.addEventListener("keydown", function(e)
{
    const UP = 38, DOWN = 40
    const BACKSPACE = 8


    if(e.keyCode == UP || e.keyCode == DOWN)
    {
        var index = visible_tags.indexOf(target_tag)
        e.keyCode == DOWN ? index++ : index--

        if(index < 0)
            index = visible_tags.length - 1
        if(index > visible_tags.length - 1)
            index = 0

        if(visible_tags[index] != undefined)
            set_target(visible_tags[index])
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

        var ev = new Event("change")
        ev.tags = [...added_tags]
        tagarea.dispatchEvent(ev)
    }
})

async function insert_tag()
{
    if(target_tag == -1)
    {
        if(!enable_creating)
            return;
        var tag = input.innerText.toLowerCase()
        while(tag.indexOf("\n") != -1)
            tag = tag.replace("\n", "")
        if(tag.length == 0)
            return;

        await create_tag(tag)
        await load_tags(false)

        var i = 0
        while(tags[i] && tags[i].name != tag)
        {
            i++
        }
        set_target(i)
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

    var form_el = document.createElement("input")
    if(form_id)
        form_el.setAttribute("form", form_id)
    form_el.setAttribute("type", "hidden")
    form_el.setAttribute("name", "tags[]")
    form_el.setAttribute("value", tag.name)
    element.appendChild(form_el)

    var ev = new Event("change")
    ev.tags = [...added_tags]
    tagarea.dispatchEvent(ev)

}

function create_tag(tag)
{
    if(!enable_creating) return;
    if(tag.trim().length < 2) return;
    var data = new FormData()
    data.append("csrf", window.csrf)
    data.append("name", tag)

    return ajax("POST", "/api/tags", data)
}

function show_closest(tag)
{
    tag = tag.toLowerCase()
    fix_tag_list_design()
    new_tag.style.display = "none"
    new_name.innerText = "[+] " + tag
    visible_tags.length = 0
    
    if(tag.trim() == "")
    {
        for(let i = 0; i < tags.length; i++)
        {
            tags[i].element.style.display = ""
            visible_tags.push(i)
        }
        if(enable_creating)
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
        visible_tags.push(i)
        
        if(current == tag)
        {
            set_target(i)
            exact_match = true
        }
    }
    if(!exact_match && tag.length > 2)
    {
        if(enable_creating)
            new_tag.style.display = ""
        set_target(enable_creating ? -1
                        : (visible_tags[0] ?? -1))
        if(!enable_creating)
            visible_tags.push(-1)
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
    if(index == -1 && !enable_creating)
        index = 0

    var prev = target_tag < 0 ? new_tag
                              : tags[target_tag].element
    prev.classList.remove("selected")

    target_tag = index;
    if(target_tag >= tags.length)
        target_tag = enable_creating ? -1 : 0
    var next = target_tag < 0 ? new_tag
                              : tags[target_tag].element
    next.classList.add("selected")
}
