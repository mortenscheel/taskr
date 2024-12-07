<?php

declare(strict_types=1);

namespace Scheel\Taskr\Config;

enum Color: string
{
    case Black = 'black';
    case Red = 'red';
    case Green = 'green';
    case Yellow = 'yellow';
    case Blue = 'blue';
    case Magenta = 'magenta';
    case Cyan = 'cyan';
    case White = 'white';
    case Gray = 'gray';
    case BrightRed = 'bright-red';
    case BrightGreen = 'bright-green';
    case BrightYellow = 'bright-yellow';
    case BrightBlue = 'bright-blue';
    case BrightMagenta = 'bright-magenta';
    case BrightCyan = 'bright-cyan';
    case BrightWhite = 'bright-white';
}
