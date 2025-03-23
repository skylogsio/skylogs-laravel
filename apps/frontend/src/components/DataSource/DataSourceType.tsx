import { Stack, Typography } from "@mui/material";

import { DATA_SOURCE_TYPE } from "@/utils/dataSourceUtils";

export default function DataSourceType({ type }: { type: string }) {
  const dataSourceType = DATA_SOURCE_TYPE.find((item) => item.value === type);

  if (!dataSourceType) return;

  return (
    <Stack direction="row" spacing={1} alignItems="center" justifyContent="center">
      {dataSourceType?.icon}
      <Typography component="div" textTransform="capitalize">
        {dataSourceType?.value}
      </Typography>
    </Stack>
  );
}
