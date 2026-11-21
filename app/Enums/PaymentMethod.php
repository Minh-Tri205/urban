<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case COD = 'COD';
    case BankTransfer = 'BankTransfer';
    case EWallet = 'EWallet';
}
