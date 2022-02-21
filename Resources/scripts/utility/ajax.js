
export function ajax(method, uri, data)
{
    return new Promise(function(resolve, reject)
    {
        "use strict"
        var request = new XMLHttpRequest();
        request.open(method, uri)

        request.addEventListener("load", function()
        {
            var answer = this.response
            if(answer != "")
                answer = JSON.parse(this.response)

            if(this.status >= 200 && this.status < 300)
            {
                resolve({
                    status: this.status,
                    statusText: this.statusText,
                    data: answer
                })
            }
            else
            {
                reject({
                    status: this.status,
                    statusText: this.statusText,
                    data: answer
                })
            }
        })

        request.addEventListener("error", function()
        {
            reject({
                status: request.status,
                statusText: request.statusText
            })
        })

        
        request.send(data)
    })
}
