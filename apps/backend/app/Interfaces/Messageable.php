<?php
namespace App\Interfaces;

interface Messageable
{
    public function defaultMessage();
    public function telegramMessage();
    public function matterMostMessage();
    public function teamsMessage();
    public function smsMessage();
    public function callMessage();
    public function emailMessage();
//    public function callMessage();
}
