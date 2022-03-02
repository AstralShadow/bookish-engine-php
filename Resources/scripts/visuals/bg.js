const enabled = true

var x = 0, y = 0

document.addEventListener("mousemove", function(ev)
{
    x = ev.clientX
    y = ev.clientY
    var w = window.innerWidth
    var h = window.innerHeight

    x = (x / w) * -10
    y = (y / h) * -10

})

var r_x = 0, r_y = 0

requestAnimationFrame(animation)
function animation()
{
    requestAnimationFrame(animation)
    r_x = (x + r_x * 15) / 16
    r_y = (y + r_y * 15) / 16

    var pos = Math.round(r_x) + "px "
            + Math.round(r_y) + "px"
    document.body.style.backgroundPosition = pos

}

