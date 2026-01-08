<?php
namespace CentralBooking\QR;

enum ErrorCorrectionCode: string
{
    case LOW = 'L';
    case MEDIUM = 'M';
    case QUARTILE = 'Q';
    case HIGH = 'H';
}