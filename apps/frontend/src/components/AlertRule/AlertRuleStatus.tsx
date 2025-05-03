import { useMemo } from "react";

import { alpha, Chip, CircularProgress, IconButton, Stack, useTheme } from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { FaFireExtinguisher } from "react-icons/fa6";
import { toast } from "react-toastify";

import { type AlertRuleStatus, IAlertRule } from "@/@types/alertRule";
import { resolveFiredAlertRule } from "@/api/alertRule/alertRule";

interface AlertRuleStatusProps {
  status: AlertRuleStatus;
  id?: IAlertRule["id"];
  onAfterResolve?: () => void;
}

export default function AlertRuleStatus({ status, onAfterResolve, id }: AlertRuleStatusProps) {
  const { palette } = useTheme();

  const { mutate: resolveAlertRule, isPending } = useMutation({
    mutationFn: () => resolveFiredAlertRule(id),
    onSuccess: (data) => {
      if (data.status) {
        onAfterResolve?.();
        toast.success("Alert Rule Resolved Successfully.");
      }
    }
  });

  const color: Record<AlertRuleStatus, string> = useMemo(
    () => ({
      resolved: palette.success.main,
      fire: palette.error.main,
      warning: palette.warning.main
    }),
    [palette]
  );

  if (!status) {
    return null;
  }

  return (
    <Stack direction="row" spacing={1} justifyContent="center">
      <Chip
        label={status}
        sx={{
          textTransform: "capitalize",
          color: color[status],
          backgroundColor: alpha(color[status], 0.07)
        }}
      />
      {status === "fire" && (
        <IconButton
          disabled={isPending}
          size="small"
          sx={{
            paddingX: 1,
            color: color[status],
            backgroundColor: `${alpha(isPending ? palette.grey[700] : color[status], 0.07)}!important`
          }}
          onClick={() => resolveAlertRule()}
        >
          {isPending ? <CircularProgress size={16} color="inherit" /> : <FaFireExtinguisher />}
        </IconButton>
      )}
    </Stack>
  );
}
