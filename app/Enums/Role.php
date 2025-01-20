<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Seller = 'seller';
}