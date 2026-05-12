<?php

class InicioController extends Controller
{
    public function index(): void
    {
        header('Location:' . URL_BASE . 'login');
        exit;
    }
}
