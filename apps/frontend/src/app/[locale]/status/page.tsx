"use client";

import { useState } from "react";

import { Box, Button, Stack, Typography } from "@mui/material";
import { useQuery } from "@tanstack/react-query";
import { HiOutlinePlusSm } from "react-icons/hi";

import { CreateUpdateModal } from "@/@types/global";
import { IStatusCard } from "@/@types/status";
import { getAllStatus } from "@/api/status";
import StatusMonitoringCards from "@/components/Status/StatusCard";
import StatusCardModal from "@/components/Status/StatusCardModal";
import StatusCardSkeleton from "@/components/Status/StatusCardSkeleton";

export default function StatusPage() {
  const [modalData, setModalData] = useState<CreateUpdateModal<IStatusCard>>(null);

  const { data: statusCards, refetch } = useQuery({
    queryKey: ["all-status-cards"],
    queryFn: () => getAllStatus(),
    refetchInterval: 5 * 1000
  });

  function renderSkeletonCards() {
    return Array.from({ length: 6 }, (_, index) => (
      <StatusCardSkeleton key={`skeleton-${index}`} />
    ));
  }

  return (
    <>
      <Stack spacing={3}>
        <Stack direction="row" justifyContent="space-between">
          <Typography variant="h5" fontSize="1.8rem" fontWeight="700" component="div">
            Status
          </Typography>
          <Button
            startIcon={<HiOutlinePlusSm size="1.3rem" />}
            onClick={() => setModalData("NEW")}
            size="small"
            variant="contained"
            sx={{ paddingRight: "1rem" }}
          >
            Create
          </Button>
        </Stack>
        <Box
          sx={{
            display: "flex",
            flexWrap: "wrap",
            gap: 3,
            paddingY: 4,
            justifyContent: "center",
            margin: "0 auto",
            borderRadius: 3,
            backgroundColor: ({ palette }) => palette.background.paper
          }}
        >
          {statusCards
            ? statusCards?.map((item) => <StatusMonitoringCards key={item.id} info={item} />)
            : renderSkeletonCards()}
        </Box>
      </Stack>
      {modalData && (
        <StatusCardModal
          data={modalData}
          open={!!modalData}
          onClose={() => setModalData(null)}
          onSubmit={() => refetch()}
        />
      )}
    </>
  );
}
