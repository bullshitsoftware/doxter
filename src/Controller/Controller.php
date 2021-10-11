<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class Controller extends AbstractController
{
    public const FLASH_SUCCESS = 'flash_success';
    public const FLASH_ERROR = 'flash_error';
}
