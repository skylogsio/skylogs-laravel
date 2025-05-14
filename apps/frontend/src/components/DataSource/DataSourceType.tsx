import { Stack, Typography } from "@mui/material";

import { DATA_SOURCE_VARIANTS, type DataSourceType } from "@/utils/dataSourceUtils";

export default function DataSourceType({ type }: { type: DataSourceType }) {
  const dataSourceType = DATA_SOURCE_VARIANTS[type];

  if (!dataSourceType) return;

  return (
    <Stack direction="row" spacing={1} alignItems="center" justifyContent="center">
      <dataSourceType.Icon size={dataSourceType.defaultSize} color={dataSourceType.defaultColor} />
      <Typography component="div" textTransform="capitalize">
        {dataSourceType?.label}
      </Typography>
    </Stack>
  );
}
