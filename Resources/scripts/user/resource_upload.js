export const form = document.querySelector("form#upload")
const popup = document.querySelector("#file_upload")
const popup_btn = document.querySelector("#open_upload")

popup_btn.addEventListener("click", function(e)
{
    const step1_required_names = ["name", "info", "tags"]
    var valid = true;
    Array.prototype.forEach
         .call(form.elements, function(el)
    {
        if(step1_required_names.indexOf(el.name) != -1)
        {
            if(!el.reportValidity())
                valid = false;
        }
    })

    if(valid)
    {
        popup.classList.remove("hidden")
    }
    e.stopPropagation()
})

popup.addEventListener("click", e => e.stopPropagation())

document.addEventListener("click", () => {
    popup.classList.add("hidden")
})


var size_feedback = document.querySelector("#rt_size_fb")
var full = document.querySelector("input[name=full]")
var demo = document.querySelector("input[name=demo]")
if(size_feedback && full && demo)
{
    [full, demo].forEach(function(input)
    {
        const span = input.parentElement
            .querySelector("span")
        input.addEventListener("change", function(ev)
        {
            var file = input.files[0]
            var limit = 20 * 1000 * 1000
            if(file.size > limit)
            {
                size_feedback.innerText =
                    "Файлът не трябва да" + 
                    "надвишава 20MB";
                this.value = "";
                span.innerText = "Избери файл"
            } else
            {
                span.innerText = file.name
            }
        })
    })
}


