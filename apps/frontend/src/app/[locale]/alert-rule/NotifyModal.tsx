import React, { useState } from "react";

import { alpha, Autocomplete, Button, IconButton, Stack, TextField } from "@mui/material";
import { useMutation, useQuery } from "@tanstack/react-query";
import { HiOutlinePlusSm, HiTrash } from "react-icons/hi";

import { IEndpoint } from "@/@types/endpoint";
import {
  addEndpointToAlertRule,
  getAlertRuleEndpointsList,
  removeEndpointFromAlertRule
} from "@/api/alertRule/alertRule";
import ModalContainer from "@/components/Modal";
import DataTable from "@/components/Table/DataTable";
import { renderEndPointChip } from "@/utils/endpointVariants";

interface NotifyModalProps {
  alertId: string;
  numberOfEndpoints: number;
  onClose: () => void;
}

export default function NotifyModal({ alertId, numberOfEndpoints, onClose }: NotifyModalProps) {
  const [open, setOpen] = useState(false);
  const [selectedEndpoints, setSelectedEndpoints] = useState<IEndpoint[]>([]);

  const { data: endpointsList, refetch } = useQuery({
    queryKey: ["notifications", alertId],
    queryFn: () => getAlertRuleEndpointsList(alertId)
  });

  const { mutate: addEndpoint, isPending: isAddingEndpoints } = useMutation({
    mutationFn: (endpointIds: string[]) => addEndpointToAlertRule(alertId, endpointIds),
    onSuccess: (data) => {
      if (data.status) {
        setSelectedEndpoints([]);
        refetch();
      }
    }
  });

  const { mutate: removeEndpoint, isPending: isRemovingEndpoint } = useMutation({
    mutationFn: (endpointId: string) => removeEndpointFromAlertRule(alertId, endpointId),
    onSuccess: (data) => {
      if (data.status) {
        refetch();
      }
    }
  });

  function handleAddEndpoint() {
    const endpointIds = selectedEndpoints.map((item) => item.id);
    if (endpointIds.length > 0) {
      addEndpoint(endpointIds);
    }
  }

  function handleClose() {
    onClose();
    setOpen(false);
  }

  return (
    <>
      <Button
        onClick={() => setOpen(true)}
        color={numberOfEndpoints > 0 ? "inherit" : "error"}
        variant="outlined"
        size="small"
        startIcon={numberOfEndpoints === 0 && <HiOutlinePlusSm size="1.3rem" />}
      >
        {numberOfEndpoints > 0 ? `View (${numberOfEndpoints})` : "Add"}
      </Button>
      <ModalContainer title="Notify" open={open} onClose={handleClose} disableEscapeKeyDown>
        <Stack marginTop={2} spacing={1}>
          <Stack direction="row" spacing={1} alignItems="center">
            <Autocomplete
              multiple
              id="endpoints"
              size="small"
              options={endpointsList?.selectableEndpoints ?? []}
              getOptionLabel={(option) => option.name}
              value={selectedEndpoints}
              onChange={(_, value) => setSelectedEndpoints(value)}
              sx={{ flex: 1 }}
              renderInput={(params) => (
                <TextField
                  {...params}
                  slotProps={{
                    input: params.InputProps,
                    inputLabel: params.InputLabelProps,
                    htmlInput: params.inputProps
                  }}
                  variant="filled"
                  label="Endpoints"
                />
              )}
            />
            <Button
              onClick={() => handleAddEndpoint()}
              disabled={selectedEndpoints.length === 0 || isAddingEndpoints}
              variant="contained"
              color="primary"
              size="large"
              startIcon={<HiOutlinePlusSm size="1.3rem" />}
            >
              Add
            </Button>
          </Stack>
          <DataTable
            data={endpointsList?.alertEndpoints ?? []}
            columns={[
              { header: "Row", size: 50, accessorFn: (_, index) => ++index },
              { header: "Name", accessorKey: "name" },
              {
                header: "Type",
                accessorKey: "type",
                cell: ({ cell }) => renderEndPointChip(cell.getValue())
              },
              {
                header: "Actions",
                cell: ({ row }) => (
                  <IconButton
                    disabled={isRemovingEndpoint}
                    onClick={() => removeEndpoint(row.original.id)}
                    sx={({ palette }) => ({
                      color: palette.error.light,
                      backgroundColor: alpha(palette.error.light, 0.05)
                    })}
                  >
                    <HiTrash size="1.4rem" />
                  </IconButton>
                )
              }
            ]}
          />
        </Stack>
      </ModalContainer>
    </>
  );
}
