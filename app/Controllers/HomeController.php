<?php

namespace App\Controllers;

use System\Core\BaseController;

/**
 * Example HomeController
 */
class HomeController extends BaseController
{
    public function index()
    {
        return $this->view('welcome', ['title' => 'AwaisPHP Framework']);
    }
}
