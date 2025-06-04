<?php

namespace App\Enums;


enum ClusterType: string
{
    case MASTER = "master";
    case AGENT = "agent";

}
