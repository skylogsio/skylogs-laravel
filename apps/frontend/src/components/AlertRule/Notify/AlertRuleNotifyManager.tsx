import { useState } from "react";

import { alpha, Autocomplete, Button, Stack, TextField, IconButton, useTheme } from "@mui/material";
import { useMutation, useQuery } from "@tanstack/react-query";
import { AiFillNotification } from "react-icons/ai";
import { HiOutlinePlusSm, HiTrash } from "react-icons/hi";

import type { IEndpoint } from "@/@types/endpoint";
import {
  addEndpointToAlertRule,
  getAlertRuleEndpointsList,
  removeEndpointFromAlertRule
} from "@/api/alertRule";
import EmptyList from "@/components/EmptyList";
import DataTable from "@/components/Table/DataTable";
import { renderEndPointChip } from "@/utils/endpointVariants";

export default function AlertRuleNotifyManager({ alertId }: { alertId: string }) {
  const { palette } = useTheme();
  const [selectedEndpoints, setSelectedEndpoints] = useState<IEndpoint[]>([]);

  const { data: endpointsList, refetch } = useQuery({
    queryKey: ["alert-rule-endpoint-list", alertId],
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

  return (
    <Stack spacing={2} marginTop={1}>
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
      {endpointsList?.alertEndpoints && endpointsList.alertEndpoints.length > 0 ? (
        <DataTable
          data={endpointsList.alertEndpoints}
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
      ) : (
        <EmptyList
          icon={
            <AiFillNotification
              size="3rem"
              style={{ color: palette.warning.light, marginBottom: "16px" }}
            />
          }
          title="No Notification Endpoints Configured"
          description="This alert rule doesn&#39;t have any notification endpoints set up yet. Use the form
            above to add endpoints like Teams, Telegram, SMS or Call to receive alerts."
        />
      )}
    </Stack>
  );
}
