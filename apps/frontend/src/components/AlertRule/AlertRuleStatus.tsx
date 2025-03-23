import { alpha, Chip, CircularProgress, IconButton, Stack, useTheme } from "@mui/material";
import { FaFireExtinguisher } from "react-icons/fa6";

import { type AlertRuleStatus } from "@/@types/alertRule";

interface AlertRuleStatusProps {
  status: AlertRuleStatus;
  onResolve?: () => void;
  loading?: boolean;
}

export default function AlertRuleStatus({ status, onResolve, loading }: AlertRuleStatusProps) {
  const { palette } = useTheme();

  if (!status) {
    return null;
  }

  const color: Record<AlertRuleStatus, string> = {
    resolved: palette.success.main,
    fire: palette.error.main,
    warning: palette.warning.main
  };

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
      {status === "fire" && onResolve && (
        <IconButton
          disabled={loading}
          size="small"
          sx={{
            paddingX: 1,
            color: color[status],
            backgroundColor: `${alpha(loading ? palette.grey[700] : color[status], 0.07)}!important`
          }}
          onClick={onResolve}
        >
          {loading ? <CircularProgress size={16} color="inherit" /> : <FaFireExtinguisher />}
        </IconButton>
      )}
    </Stack>
  );
}
