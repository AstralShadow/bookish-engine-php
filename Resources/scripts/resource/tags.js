const area = document.querySelector("#tags_container")

window.tags.forEach(function(tag){
    const element = document.createElement("tag-box")
    element.innerText = tag.name
    area.appendChild(element)
})
