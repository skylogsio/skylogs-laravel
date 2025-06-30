import { alpha, Chip, CircularProgress, useTheme } from "@mui/material";
import { useQuery } from "@tanstack/react-query";

import type { IDataSource } from "@/@types/dataSource";
import { getDataSourceStatus } from "@/api/dataSource";

export default function DataSourceConnectionStatus({
  dataSourceId
}: {
  dataSourceId: IDataSource["id"];
}) {
  const { palette } = useTheme();

  const { data, isPending } = useQuery({
    queryKey: ["data-source-status", dataSourceId],
    queryFn: () => getDataSourceStatus(dataSourceId),
    enabled: Boolean(dataSourceId),
    refetchInterval: 10 * 1000
  });

  if (isPending) {
    return <CircularProgress size={14} />;
  }

  const color = data?.isConnected ? palette.success.main : palette.error.main;
  const label = data?.isConnected ? "Connected" : "Disconnected";

  return (
    <Chip
      label={label}
      sx={{
        textTransform: "capitalize",
        color: color,
        backgroundColor: alpha(color, 0.07)
      }}
    />
  );
}
