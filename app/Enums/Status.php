<?php

namespace App\Enums;

enum Status: string
{
    //license status
    case active = 'ACTIVE';
    case inactive = 'INACTIVE';
    case suspended = 'SUSPENDED';
    case submitted = 'SUBMITTED';
    case finish = 'COMPLETED';
    case denied = 'DENIED';
    case registered = 'REGISTERED';
    
}