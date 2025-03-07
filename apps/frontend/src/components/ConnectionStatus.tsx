import { alpha, Chip, useTheme } from "@mui/material";

import type { ConnectionStatusType } from "@/@types/global";

export default function ConnectionStatus({ status }: { status: ConnectionStatusType }) {
  const { palette } = useTheme();
  const color: Record<ConnectionStatusType, string> = {
    connected: palette.success.main,
    disconnected: palette.error.main,
    warning: palette.warning.main
  };
  return (
    <Chip
      label={status}
      sx={{
        textTransform: "capitalize",
        color: color[status],
        backgroundColor: alpha(color[status], 0.07)
      }}
    />
  );
}
