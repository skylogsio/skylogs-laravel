import { alpha, Chip, useTheme } from "@mui/material";
import { useQuery } from "@tanstack/react-query";

import type { IDataSource } from "@/@types/dataSource";
import { getDataSourceStatus } from "@/api/dataSource";

export default function DataSourceConnectionStatus({
  dataSourceId
}: {
  dataSourceId: IDataSource["id"];
}) {
  const { palette } = useTheme();

  const { data } = useQuery({
    queryKey: ["data-source-status", dataSourceId],
    queryFn: () => getDataSourceStatus(dataSourceId),
    enabled: Boolean(dataSourceId),
    refetchInterval: 10 * 1000
  });

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
