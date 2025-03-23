import { Stack, Typography } from "@mui/material";

import { ALERT_RULE_TYPE } from "@/utils/alertRuleUtils";

export default function AlertRuleType({ type }: { type: string }) {
  const alertRuleType = ALERT_RULE_TYPE.find((item) => item.value === type);

  if (!alertRuleType) return;

  return (
    <Stack direction="row" spacing={1} alignItems="center" justifyContent="center">
      {alertRuleType?.icon}
      <Typography component="div" textTransform="capitalize">
        {alertRuleType?.value}
      </Typography>
    </Stack>
  );
}
