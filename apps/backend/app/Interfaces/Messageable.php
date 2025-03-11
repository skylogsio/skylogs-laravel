<?php
namespace App\Interfaces;

interface Messageable
{
    public function telegramMessage();
    public function teamsMessage();
    public function smsMessage();
    public function callMessage();
    public function emailMessage();
//    public function callMessage();
}
