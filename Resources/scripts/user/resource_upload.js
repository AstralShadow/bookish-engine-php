export const form = document.querySelector("form#upload")
const popup = document.querySelector("#file_upload")
const popup_btn = document.querySelector("#open_upload")

popup_btn.addEventListener("click", function()
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
        popup.classList.add("shown")
    }
})
