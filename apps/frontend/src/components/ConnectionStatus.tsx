import { alpha, Chip, CircularProgress, useTheme } from "@mui/material";
import { useQuery } from "@tanstack/react-query";

import type { ICluster } from "@/@types/cluster";
import type { IDataSource } from "@/@types/dataSource";
import { getClusterStatus } from "@/api/cluster";
import { getDataSourceStatus } from "@/api/dataSource";

interface ConnectionStatusProps {
  dataSourceId?: IDataSource["id"];
  clusterId?: ICluster["id"];
}

export default function ConnectionStatus({ dataSourceId, clusterId }: ConnectionStatusProps) {
  const { palette } = useTheme();

  const { data: dataSourceStatus, isPending: isPendingDataSourceStatus } = useQuery({
    queryKey: ["data-source-status", dataSourceId],
    queryFn: () => getDataSourceStatus(dataSourceId!),
    enabled: Boolean(dataSourceId),
    refetchInterval: 10 * 1000
  });

  const { data: clusterStatus, isPending: isPendingClusterStatus } = useQuery({
    queryKey: ["cluster-status", clusterId],
    queryFn: () => getClusterStatus(clusterId!),
    enabled: Boolean(clusterId),
    refetchInterval: 10 * 1000
  });

  if (isPendingClusterStatus || isPendingDataSourceStatus) {
    return <CircularProgress size={14} />;
  }

  let color: string;
  let label: "Connected" | "Disconnected";
  if (dataSourceId) {
    color = dataSourceStatus?.isConnected ? palette.success.main : palette.error.main;
    label = dataSourceStatus?.isConnected ? "Connected" : "Disconnected";
  } else if (clusterStatus) {
    color = clusterStatus?.isConnected ? palette.success.main : palette.error.main;
    label = clusterStatus?.isConnected ? "Connected" : "Disconnected";
  } else {
    return "-";
  }

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
