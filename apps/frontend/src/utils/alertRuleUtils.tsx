import { lightBlue } from "@mui/material/colors";
import { FaCloud } from "react-icons/fa";

import { DATA_SOURCE_TYPE } from "@/utils/dataSourceUtils";

export const ALERT_RULE_TYPE = [
  {
    value: "api",
    icon: <FaCloud color={lightBlue[500]} size="1.2rem" />
  },
  ...DATA_SOURCE_TYPE
];
