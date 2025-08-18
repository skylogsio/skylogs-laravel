"use client";

import { useState } from "react";

import { Button, Stack, Typography } from "@mui/material";
import { useQuery } from "@tanstack/react-query";
import { HiOutlinePlusSm } from "react-icons/hi";

import { CreateUpdateModal } from "@/@types/global";
import { IStatusCard } from "@/@types/status";
import { getAllStatus } from "@/api/status";
import StatusCardModal from "@/components/Status/StatusCardModal";

export default function StatusPage() {
  const [modalData, setModalData] = useState<CreateUpdateModal<IStatusCard>>(null);

  const { data: statusCards, refetch } = useQuery({
    queryKey: ["all-status-cards"],
    queryFn: () => getAllStatus()
  });

  return (
    <>
      <Stack>
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
