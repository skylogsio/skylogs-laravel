import { Stack, Typography } from "@mui/material";

import { ALERT_RULE_VARIANTS, type AlertRuleType } from "@/utils/alertRuleUtils";

export default function AlertRuleType({ type }: { type: AlertRuleType }) {
  const alertRuleType = ALERT_RULE_VARIANTS[type];

  if (!alertRuleType) return;

  return (
    <Stack direction="row" spacing={1} alignItems="center" justifyContent="center">
      <alertRuleType.Icon size={alertRuleType.defaultSize} color={alertRuleType.defaultColor} />
      <Typography component="div" textTransform="capitalize">
        {alertRuleType.label}
      </Typography>
    </Stack>
  );
}
