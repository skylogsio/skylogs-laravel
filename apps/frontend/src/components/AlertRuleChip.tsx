import { Stack, Typography } from "@mui/material";

import { ALERT_RULE_TYPE } from "@/utils/alertRuleUtils";

export default function AlertRuleChip({ type }: { type: string }) {
  const dataSourceType = ALERT_RULE_TYPE.find((item) => item.value === type);

  if (!dataSourceType) return;

  return (
    <Stack direction="row" spacing={1} alignItems="center" justifyContent="center">
      {dataSourceType?.icon}
      <Typography component="div" textTransform="capitalize">
        {dataSourceType?.value}
      </Typography>
    </Stack>
  );
}
