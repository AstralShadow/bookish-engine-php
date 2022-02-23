import { ajax } from "../utility/ajax.js"
const area = document.querySelector("tag-area")
const content = document.querySelector("#search_results")

const sample = document.querySelector(".search_result")
while(content.firstChild)
    content.removeChild(content.firstChild)

area.addEventListener("change", async function(e)
{
    var query = e.tags.join("+")

    var result = await ajax("GET", "/api/filter/" + query)
    if(!result.data){
        console.log("ajax", result)
        return;
    }

    var resources = result.data
    while(content.firstChild)
        content.removeChild(content.firstChild)

    resources.forEach(displayResource)
})

var cache = {} 
function displayResource(data)
{
    const new_block = cache[data.id] == undefined
    var block = new_block ? sample.cloneNode(true)
                          : cache[data.id]
    content.appendChild(block)
    
    var raw_keys = ["name", "owner", "created", "info"]
    raw_keys.forEach((key) => {
        var element = block.querySelector
            ("[data-content=\""+key+"\"]")

        if(!element)
            return;
        
        element.innerText = data[key] ?? ""
        element.value = data[key] ?? ""
    })

    /* Tags */
    var tag_area = block.querySelector("tag-area")
    if(tag_area)
    {
        data.tags.forEach(function(tag)
        {
            var base = document.createElement("tag-box")
            var name = document.createElement("tag-name")
            var info = document.createElement("tag-info")
            tag_area.appendChild(base)

            name.innerText = tag.name ?? ""
            info.innerText = tag.info ?? ""

            base.appendChild(name)
            base.appendChild(info)
        })
    }

    /* Resource page */
    var resource = block.querySelector(".review")
    if(resource)
    {
        if(new_block)
        {
            resource.addEventListener("click", function()
            {
                var id = "/resource/" + data.id
                window.open(id, '_blank'); 
            })
        }
    }
}
