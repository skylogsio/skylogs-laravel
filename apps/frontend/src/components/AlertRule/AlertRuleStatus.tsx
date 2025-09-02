import { useMemo } from "react";

import {
  alpha,
  Chip,
  type ChipProps,
  CircularProgress,
  IconButton,
  Stack,
  Tooltip,
  useTheme
} from "@mui/material";
import { useMutation } from "@tanstack/react-query";
import { FaHandsHelping, FaTools } from "react-icons/fa";
import { toast } from "react-toastify";

import { type AlertRuleStatus, IAlertRule } from "@/@types/alertRule";
import { resolveFiredAlertRule } from "@/api/alertRule";

interface AlertRuleStatusProps extends Pick<ChipProps, "size"> {
  status: AlertRuleStatus;
  id?: IAlertRule["id"];
  onAfterResolve?: () => void;
  statusTitle?: string;
}

export default function AlertRuleStatus({
  status,
  onAfterResolve,
  id,
  statusTitle,
  size = "medium"
}: AlertRuleStatusProps) {
  const { palette } = useTheme();

  const { mutate: resolveAlertRule, isPending } = useMutation({
    mutationFn: () => resolveFiredAlertRule(id!),
    onSuccess: (data) => {
      if (data.status) {
        onAfterResolve?.();
        toast.success("Alert Rule Resolved Successfully.");
      }
    }
  });

  const color: Record<Exclude<AlertRuleStatus, "unknown">, string> = useMemo(
    () => ({
      resolved: palette.success.main,
      critical: palette.error.main,
      warning: palette.warning.main,
      triggered: palette.grey[100]
    }),
    [palette]
  );

  if (!status || status === "unknown") {
    return "-";
  }

  return (
    <Stack direction="row" spacing={1} justifyContent="center">
      <Chip
        label={statusTitle ?? status}
        size={size}
        sx={{
          textTransform: "capitalize",
          color: color[status],
          backgroundColor: alpha(color[status], 0.07)
        }}
      />
      {status === "critical" && id && (
        <Stack direction="row" spacing={1}>
          <Tooltip title="Resolve" placement="top" arrow>
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
              {isPending ? <CircularProgress size={17} color="inherit" /> : <FaTools />}
            </IconButton>
          </Tooltip>
          <Tooltip title="Acknowledge" placement="top" arrow>
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
              {isPending ? <CircularProgress size={17} color="inherit" /> : <FaHandsHelping />}
            </IconButton>
          </Tooltip>
        </Stack>
      )}
    </Stack>
  );
}
