import { lightBlue } from "@mui/material/colors";
import type { IconType } from "react-icons";
import { FaCloud } from "react-icons/fa";

import { DATA_SOURCE_VARIANTS, type DataSourceType } from "@/utils/dataSourceUtils";

export type AlertRuleType = DataSourceType | "api";

export const ALERT_RULE_VARIANTS: Record<
  AlertRuleType,
  {
    label: string;
    Icon: IconType;
    defaultColor: string;
    defaultSize: string;
  }
> = {
  api: {
    label: "Api",
    defaultColor: lightBlue[500],
    defaultSize: "1.2rem",
    Icon: FaCloud
  },
  ...DATA_SOURCE_VARIANTS
};
