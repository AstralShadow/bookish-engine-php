<?php
namespace Controllers;

use Core\Request;
use Core\Responses\TemplateResponse;
use Core\RequestMethods\GET;
use Core\RequestMethods\PUT;
use Core\RequestMethods\POST;
use Core\RequestMethods\DELETE;
use Core\RequestMethods\Fallback;
use Core\RequestMethods\StartUp;


class Home
{

//    #[StartUp]
//    public static function turnOnDebug(): void
//    { }

    #[GET]
    public static function index()
    {
        $response = new TemplateResponse(file: "index.html", code: 501);

        return $response;
    }

/*
    #[GET("/{name}")]
    public static function welcome(Request $req)
    {
        $response = new TemplateResponse(file: "index.html", code: 501);
        $response->setValue("Message", $req->name);

        return $response;
    }

    #[GET("/Anonymous")]
    public static function hiAnon()
    {
        $response = new TemplateResponse(file: "index.html", code: 501);
        $response->setValue("Message", "fellow brother");

        return $response;
    }

*/
    #[Fallback]
    public static function notFound()
    {
        return new TemplateResponse(file: "404.html", code: 404);
    }

}
