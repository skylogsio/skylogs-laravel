import React, { useMemo } from "react";

import { Button, CircularProgress, Stack, Typography, useTheme } from "@mui/material";
import { useInfiniteQuery } from "@tanstack/react-query";
import { HiChevronDoubleDown } from "react-icons/hi";

import { IAlertRule, IPrometheusAlertHistory } from "@/@types/alertRule";
import { getAlertRuleHistory } from "@/api/alertRule";
import DataTable from "@/components/Table/DataTable";

export default function PrometheusAlertsHistory({ alertId }: { alertId: IAlertRule["id"] }) {
  const { palette } = useTheme();

  const { data, fetchNextPage, hasNextPage, isFetching, isFetchingNextPage } = useInfiniteQuery({
    queryKey: ["alertRuleHistory", alertId],
    queryFn: ({ pageParam }) => getAlertRuleHistory<IPrometheusAlertHistory>(alertId, pageParam),
    initialPageParam: 1,
    getNextPageParam: (lastPage) =>
      lastPage.next_page_url ? lastPage.current_page + 1 : undefined,
    refetchOnWindowFocus: false
  });

  console.log(data);

  const allData = useMemo(() => {
    if (!data?.pages) return [];
    return data.pages.flatMap((page) => page.data);
  }, [data]);

  const totalCount = data?.pages?.[0]?.total || 0;

  if (isFetching && !isFetchingNextPage) {
    return null;
  }

  return (
    <Stack alignItems="center">
      <DataTable<IPrometheusAlertHistory>
        data={allData}
        columns={[
          { header: "Row", accessorFn: (_, index) => ++index },
          { header: "Fired Instances", accessorKey: "countFire" },
          { header: "Resolved Instances", accessorKey: "countResolve" },
          { header: "Date", accessorKey: "createdAt" }
        ]}
      />
      <Stack alignItems="center" position="relative" width="100%" paddingBottom={1}>
        <Typography
          variant="caption"
          color="text.secondary"
          sx={{ position: "absolute", right: 10, top: 6 }}
        >
          Showing {allData.length} of {totalCount}
        </Typography>
        {hasNextPage && (
          <Button
            endIcon={
              isFetching ? <CircularProgress color="inherit" size={14} /> : <HiChevronDoubleDown />
            }
            onClick={() => fetchNextPage()}
            disabled={isFetching}
            sx={{
              marginX: "auto",
              marginTop: 2,
              backgroundColor: palette.background.paper,
              border: "1px solid",
              paddingX: 2,
              borderColor: palette.secondary.light,
              color: palette.secondary.dark
            }}
          >
            Load More
          </Button>
        )}
      </Stack>
    </Stack>
  );
}
