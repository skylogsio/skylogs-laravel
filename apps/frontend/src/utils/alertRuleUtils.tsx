import { lightBlue } from "@mui/material/colors";
import { FaCloud } from "react-icons/fa";

import { DATA_SOURCE_VARIANTS, type DataSourceType } from "@/utils/dataSourceUtils";

export const ALERT_RULE_VARIANTS = [
  {
    value: "api",
    icon: <FaCloud color={lightBlue[500]} size="1.2rem" />
  },
  ...DATA_SOURCE_VARIANTS
];

export type AlertRuleType = DataSourceType | "api";
