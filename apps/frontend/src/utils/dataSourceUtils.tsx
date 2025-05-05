import { type ReactNode } from "react";

import { blue, grey, orange, red, yellow } from "@mui/material/colors";
import { FaBell } from "react-icons/fa";
import { SiGrafana, SiMetabase, SiPrometheus, SiSentry, SiVictoriametrics } from "react-icons/si";

import ElasticIcon from "@/assets/svg/ElasticIcon";
import PerconaIcon from "@/assets/svg/PerconaIcon";
import SplunkIcon from "@/assets/svg/SplunkIcon";
import ZabbixIcon from "@/assets/svg/ZabbixIcon";

export type DataSourceType =
  | "prometheus"
  | "notification"
  | "sentry"
  | "grafana"
  | "metabase"
  | "elastic"
  | "zabbix"
  | "splunk"
  | "victoriametrics"
  | "pmm";

export const DATA_SOURCE_VARIANTS: Array<{ value: DataSourceType; icon: ReactNode }> = [
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
    icon: <SplunkIcon size="1.2rem" />
  },
  {
    value: "victoriametrics",
    icon: <SiVictoriametrics size="1.2rem" />
  },
  {
    value: "pmm",
    icon: <PerconaIcon size="1.2rem" />
  }
];
