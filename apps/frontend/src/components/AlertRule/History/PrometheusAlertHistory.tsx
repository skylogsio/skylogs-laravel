import React, { useMemo, useState } from "react";

import {
  alpha,
  Avatar,
  Button,
  Chip,
  CircularProgress,
  IconButton,
  Stack,
  Typography,
  useTheme
} from "@mui/material";
import { useInfiniteQuery } from "@tanstack/react-query";
import JsonView from "@uiw/react-json-view";
import { githubLightTheme } from "@uiw/react-json-view/githubLight";
import { HiChevronDoubleDown, HiInformationCircle } from "react-icons/hi";

import type { IAlertRule, IPrometheusAlertHistory } from "@/@types/alertRule";
import { getAlertRuleHistory } from "@/api/alertRule";
import AlertRuleStatus from "@/components/AlertRule/AlertRuleStatus";
import ModalContainer from "@/components/Modal";
import DataTable from "@/components/Table/DataTable";

export default function PrometheusAlertsHistory({ alertId }: { alertId: IAlertRule["id"] }) {
  const { palette } = useTheme();
  const [details, setDetails] = useState<IPrometheusAlertHistory | null>(null);

  const { data, fetchNextPage, hasNextPage, isFetching, isFetchingNextPage } = useInfiniteQuery({
    queryKey: ["alertRuleHistory", alertId],
    queryFn: ({ pageParam }) => getAlertRuleHistory<IPrometheusAlertHistory>(alertId, pageParam),
    initialPageParam: 1,
    getNextPageParam: (lastPage) =>
      lastPage.next_page_url ? lastPage.current_page + 1 : undefined,
    refetchOnWindowFocus: false
  });

  const allData = useMemo(() => {
    if (!data?.pages) return [];
    return data.pages.flatMap((page) => page.data);
  }, [data]);

  const totalCount = data?.pages?.[0]?.total || 0;

  function renderFiredAndResolvedChip(countFire: number, countResolve: number) {
    const numberOfFired = countFire > 100 ? "+99" : countFire;
    const numberOfResolved = countResolve > 100 ? "+99" : countResolve;
    return (
      <Stack direction="row" spacing={1} justifyContent="center">
        {countFire > 0 && (
          <Chip
            avatar={
              <Avatar
                variant="rounded"
                sx={{
                  bgcolor: ({ palette }) => alpha(palette.error.light, 0.3),
                  color: ({ palette }) => `${palette.error.main}!important`
                }}
              >
                {numberOfFired}
              </Avatar>
            }
            variant="outlined"
            label="Fired"
            color="error"
          />
        )}
        {countResolve > 0 && (
          <Chip
            avatar={
              <Avatar
                variant="rounded"
                sx={{
                  bgcolor: ({ palette }) => alpha(palette.success.light, 0.3),
                  color: ({ palette }) => `${palette.success.main}!important`
                }}
              >
                {numberOfResolved}
              </Avatar>
            }
            variant="outlined"
            label="Resolved"
            color="success"
          />
        )}
      </Stack>
    );
  }

  if (isFetching && !isFetchingNextPage) {
    return null;
  }

  return (
    <>
      <Stack alignItems="center">
        <DataTable<IPrometheusAlertHistory>
          data={allData}
          onRowClick={(row) => setDetails(row)}
          columns={[
            { header: "Row", accessorFn: (_, index) => ++index },
            {
              header: "Fired / Resolved",
              cell: ({ row }) =>
                renderFiredAndResolvedChip(row.original.countFire, row.original.countResolve)
            },
            { header: "Date", accessorKey: "createdAt" },
            {
              header: "Actions",
              cell: ({ row }) => (
                <IconButton onClick={() => setDetails(row.original)}>
                  <HiInformationCircle color={palette.primary.light} />
                </IconButton>
              )
            }
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
                isFetching ? (
                  <CircularProgress color="inherit" size={14} />
                ) : (
                  <HiChevronDoubleDown />
                )
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
      {details && (
        <ModalContainer
          title="History Details"
          maxWidth="70vw"
          open={Boolean(details)}
          onClose={() => setDetails(null)}
        >
          <Stack height="70vh" overflow="auto" paddingRight={1} spacing={2} marginTop={2}>
            {details.alerts.map((alert, index) => (
              <Stack
                key={index}
                sx={{
                  borderRadius: 2,
                  border: "1px solid",
                  borderColor: palette.grey[100],
                  padding: 2,
                  paddingTop: 1
                }}
                spacing={1}
              >
                <Stack direction="row" spacing={2}>
                  <Typography variant="body1" fontWeight="bold">
                    {alert.alertRuleName}
                  </Typography>
                  <AlertRuleStatus
                    size="small"
                    status={alert.skylogsStatus === 2 ? "critical" : "resolved"}
                  />
                </Stack>
                <Stack direction="row" width="100%" spacing={2}>
                  <Stack
                    direction="row"
                    padding={1}
                    bgcolor={palette.background.default}
                    borderRadius={2}
                    spacing={1}
                    flexWrap="wrap"
                    width="50%"
                  >
                    <Typography variant="body2" sx={{ opacity: 0.6 }}>
                      Data Source:
                    </Typography>
                    <Typography variant="body2">{alert.dataSourceName}</Typography>
                  </Stack>
                  <Stack
                    direction="row"
                    padding={1}
                    bgcolor={palette.background.default}
                    borderRadius={2}
                    spacing={1}
                    flexWrap="wrap"
                    width="50%"
                  >
                    <Typography variant="body2" sx={{ opacity: 0.6 }}>
                      Data Source Alert Name:
                    </Typography>
                    <Typography variant="body2">{alert.dataSourceAlertName}</Typography>
                  </Stack>
                </Stack>
                <Stack
                  direction="row-reverse"
                  width="100%"
                  spacing={2}
                  sx={{ "& .w-json-view-container": { backgroundColor: "transparent !important" } }}
                >
                  <Stack
                    direction="row"
                    width="50%"
                    padding={1}
                    bgcolor={palette.background.default}
                    borderRadius={2}
                    spacing={1}
                    flexWrap="wrap"
                  >
                    <Typography variant="body2" sx={{ opacity: 0.6 }}>
                      Annotations:
                    </Typography>
                    <JsonView
                      collapsed={0}
                      style={githubLightTheme}
                      value={alert.annotations}
                      enableClipboard={false}
                    />
                  </Stack>
                  <Stack
                    direction="row"
                    width="50%"
                    padding={1}
                    bgcolor={palette.background.default}
                    borderRadius={2}
                    spacing={1}
                    flexWrap="wrap"
                  >
                    <Typography variant="body2" sx={{ opacity: 0.6 }}>
                      Labels:
                    </Typography>
                    <JsonView
                      collapsed={0}
                      style={githubLightTheme}
                      value={alert.labels}
                      enableClipboard={false}
                    />
                  </Stack>
                </Stack>
              </Stack>
            ))}
          </Stack>
        </ModalContainer>
      )}
    </>
  );
}
