import { blue, grey, orange, red, yellow } from "@mui/material/colors";
import { FaBell } from "react-icons/fa";
import {
  SiGrafana,
  SiMetabase,
  SiPrometheus,
  SiSentry,
  SiSplunk,
  SiVictoriametrics
} from "react-icons/si";

import ElasticIcon from "@/assets/svg/ElasticIcon";
import PerconaIcon from "@/assets/svg/PerconaIcon";
import ZabbixIcon from "@/assets/svg/ZabbixIcon";

export const DATA_SOURCE_TYPE = [
  {
    value: "prometheus",
    icon: <SiPrometheus color={red[500]} size="1.2rem" />
  },
  {
    value: "notification",
    icon: <FaBell color={yellow[600]} size="1.2rem" />
  },
  {
    value: "sentry",
    icon: <SiSentry color={grey[700]} size="1.2rem" />
  },
  {
    value: "grafana",
    icon: <SiGrafana color={orange[500]} size="1.2rem" />
  },
  {
    value: "metabase",
    icon: <SiMetabase color={blue[600]} size="1.2rem" />
  },
  {
    value: "elastic",
    icon: <ElasticIcon size="1.2rem" />
  },
  {
    value: "zabbix",
    icon: <ZabbixIcon size="1.2rem" />
  },
  {
    value: "splunk",
    icon: <SiSplunk size="1.2rem" />
  },
  {
    value: "victoriametrics",
    icon: <SiVictoriametrics size="1.2rem" />
  },
  {
    value: "Percona",
    icon: <PerconaIcon size="1.2rem" />
  }
];
